<template>
    <div class="q-pa-md">
        <StandardTable
            title="Productos / Servicios"
            :columns="columns"
            :rows="productos"
            :loading="loading"
            :filter="filter"
            @request="loadProductos"
            @new="openDialog(null)"
            @edit="openDialog"
            @delete="deleteProducto"
            @toggle-status="toggleStatus"
        >
            <template v-slot:body-cell-costo="props">
                <q-td :props="props">
                    {{ formatCurrency(props.row.costo) }}
                </q-td>
            </template>
            <template v-slot:body-cell-precio_venta="props">
                <q-td :props="props">
                    {{ formatCurrency(props.row.precio_venta) }}
                </q-td>
            </template>
            <template v-slot:body-cell-margen="props">
                <q-td :props="props">
                    <q-badge
                        :color="calculateMargin(props.row) > 0 ? 'blue-grey' : 'orange'"
                        outline
                    >
                        {{ calculateMargin(props.row) }}%
                    </q-badge>
                </q-td>
            </template>
            <template v-slot:body-cell-is_active="props">
                <q-td :props="props">
                    <q-badge
                        :color="props.row.is_active ? 'positive' : 'grey'"
                        :label="props.row.is_active ? 'Activo' : 'Inactivo'"
                    />
                </q-td>
            </template>
            <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                    <q-btn
                        flat
                        round
                        dense
                        color="primary"
                        icon="edit"
                        @click="openDialog(props.row)"
                    >
                        <q-tooltip>Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                        flat
                        round
                        dense
                        :color="props.row.is_active ? 'negative' : 'positive'"
                        :icon="props.row.is_active ? 'block' : 'check_circle'"
                        @click="toggleStatus(props.row)"
                    >
                        <q-tooltip>{{
                            props.row.is_active ? "Desactivar" : "Activar"
                        }}</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </StandardTable>

        <q-dialog v-model="dialog" persistent transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 500px" class="modern-dialog">
                <!-- HEADER -->
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon name="inventory_2" size="24px" class="q-mr-sm" />
                    <div class="text-h6">
                        {{ editingProducto ? "Editar Producto" : "Nuevo Producto" }}
                    </div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <!-- FORMULARIO -->
                <q-card-section class="q-pt-md">
                    <q-form @submit="saveProducto" class="q-gutter-md">
                        <div class="row q-col-gutter-sm">
                            <div class="col-12 col-sm-6">
                                <q-input
                                    v-model="form.codigo"
                                    label="Código *"
                                    outlined
                                    dense
                                    :rules="[(val) => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="qr_code" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>
                            <div class="col-12 col-sm-6 flex items-center">
                                <q-checkbox
                                    v-model="form.is_active"
                                    label="Activo"
                                    dense
                                />
                            </div>
                        </div>

                        <q-input
                            v-model="form.nombre"
                            label="Nombre *"
                            outlined
                            dense
                            :rules="[(val) => !!val || 'Requerido']"
                        >
                            <template v-slot:prepend>
                                <q-icon name="label" color="primary" size="xs" />
                            </template>
                        </q-input>

                        <q-input
                            v-model="form.descripcion"
                            label="Descripción"
                            outlined
                            dense
                            type="textarea"
                            rows="2"
                        >
                            <template v-slot:prepend>
                                <q-icon name="description" color="primary" size="xs" />
                            </template>
                        </q-input>

                        <div class="row q-col-gutter-sm">
                            <div class="col-12 col-sm-6">
                                <q-input
                                    v-model.number="form.costo"
                                    label="Costo Base *"
                                    outlined
                                    dense
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    prefix="$"
                                    :rules="[(val) => val >= 0 || 'Inválido']"
                                />
                            </div>
                            <div class="col-12 col-sm-6">
                                <q-input
                                    v-model.number="form.precio_venta"
                                    label="Precio Venta *"
                                    outlined
                                    dense
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    prefix="$"
                                    :rules="[(val) => val >= 0 || 'Inválido']"
                                />
                            </div>
                        </div>

                        <!-- Info de Margen -->
                        <div
                            v-if="form.costo && form.precio_venta"
                            class="q-px-sm q-py-xs bg-grey-2 rounded-borders text-right"
                        >
                            <div class="text-caption text-grey-8">
                                Utilidad Bruta:
                                <strong>{{
                                    formatCurrency(
                                        form.precio_venta - form.costo
                                    )
                                }}</strong>
                                ({{
                                    (
                                        ((form.precio_venta - form.costo) /
                                            form.precio_venta) *
                                        100
                                    ).toFixed(2)
                                }}%)
                            </div>
                        </div>

                        <!-- Submit buttom hidden to allow enter submit -->
                        <button type="submit" style="display: none"></button>
                    </q-form>
                </q-card-section>

                <q-separator />

                <!-- FOOTER -->
                <q-card-section class="row justify-end q-pt-xs q-pb-sm">
                    <q-btn
                        label="Cancelar"
                        color="grey-7"
                        flat
                        v-close-popup
                        class="q-mr-sm"
                    />
                    <q-btn
                        :label="editingProducto ? 'Actualizar' : 'Guardar'"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="saveProducto"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useQuasar } from "quasar";
import StandardTable from "@/Components/Shared/StandardTable.vue";
import axios from "axios";

const $q = useQuasar();

// Data
const productos = ref([]);
const loading = ref(false);
const filter = ref("");
const dialog = ref(false);
const saving = ref(false);
const editingProducto = ref(null);

const form = ref({
    codigo: "",
    nombre: "",
    descripcion: "",
    costo: 0,
    precio_venta: 0,
    is_active: true,
});

// Columns
const columns = [
    {
        name: "codigo",
        required: true,
        label: "Código",
        align: "left",
        field: "codigo",
        sortable: true,
    },
    {
        name: "nombre",
        required: true,
        label: "Nombre",
        align: "left",
        field: "nombre",
        sortable: true,
    },
    {
        name: "costo",
        label: "Costo",
        align: "right",
        field: "costo",
        sortable: true,
    },
    {
        name: "precio_venta",
        label: "Precio Venta",
        align: "right",
        field: "precio_venta",
        sortable: true,
    },
    {
        name: "margen",
        label: "Margen",
        align: "right",
        field: (row) => calculateMargin(row),
        sortable: false,
    },
    {
        name: "is_active",
        label: "Estado",
        align: "center",
        field: "is_active",
        sortable: true,
    },
    { name: "actions", label: "Acciones", align: "center" },
];

const loadProductos = async (props = null) => {
    loading.value = true;
    try {
        const page = props?.pagination?.page || 1;
        const rowsPerPage = props?.pagination?.rowsPerPage || 10;
        const sortBy = props?.pagination?.sortBy || "nombre";
        const descending = props?.pagination?.descending || false;

        const response = await axios.get("/api/productos", {
            params: {
                page,
                per_page: rowsPerPage,
                sort: sortBy,
                direction: descending ? "desc" : "asc",
                search: filter.value,
            },
        });

        productos.value = response.data;
    } catch (error) {
        console.error("Error loading productos:", error);
        $q.notify({
            type: "negative",
            message: "Error al cargar productos",
        });
    } finally {
        loading.value = false;
    }
};

const openDialog = (producto = null) => {
    editingProducto.value = producto;
    if (producto) {
        form.value = { ...producto, is_active: !!producto.is_active };
    } else {
        form.value = {
            codigo: "",
            nombre: "",
            descripcion: "",
            costo: 0,
            precio_venta: 0,
            is_active: true,
        };
    }
    dialog.value = true;
};

const saveProducto = async () => {
    saving.value = true;
    try {
        if (editingProducto.value) {
            await axios.put(
                `/api/productos/${editingProducto.value.id}`,
                form.value
            );
            $q.notify({
                type: "positive",
                message: "Producto actualizado correctamente",
            });
        } else {
            await axios.post("/api/productos", form.value);
            $q.notify({
                type: "positive",
                message: "Producto creado correctamente",
            });
        }
        dialog.value = false;
        loadProductos();
    } catch (error) {
        console.error("Error saving producto:", error);
        const msg =
            error.response?.data?.message || "Error al guardar el producto";
        $q.notify({ type: "negative", message: msg });
        
        // Mostrar errores de validación específicos si existen
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(err => {
                 $q.notify({ type: "negative", message: err[0], timeout: 2000 });
            });
        }
    } finally {
        saving.value = false;
    }
};

const deleteProducto = async (row) => {
    try {
        await axios.delete(`/api/productos/${row.id}`);
        $q.notify({
            type: "positive",
            message: "Producto eliminado correctamente",
        });
        loadProductos();
    } catch (error) {
        console.error("Error toggling status:", error);
        $q.notify({ type: "negative", message: "Error al eliminar" });
    }
};

const toggleStatus = async (row) => {
    try {
        await axios.patch(`/api/productos/${row.id}/toggle-status`);
        $q.notify({
            type: "positive",
            message: "Estado actualizado",
        });
        loadProductos();
    } catch (error) {
        console.error("Error toggling status:", error);
        $q.notify({
            type: "negative",
            message: "Error al cambiar estado",
        });
    }
};

// Utilidades
const formatCurrency = (amount) => {
    return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0, // En COP no solemos usar centavos
    }).format(amount);
};

const calculateMargin = (row) => {
    if (!row.precio_venta || row.precio_venta == 0) return 0;
    return (
        ((row.precio_venta - row.costo) / row.precio_venta) *
        100
    ).toFixed(1);
};

onMounted(() => {
    loadProductos();
});
</script>

<style scoped>
/* Dialog moderno */
.modern-dialog {
    border-radius: 8px;
    overflow: hidden;
}

/* Tabla más compacta para productos */
:deep(.q-table tbody td) {
    height: 32px !important;
    padding: 2px 8px !important;
}
:deep(.q-table tbody tr) {
    height: 32px !important;
}
</style>
