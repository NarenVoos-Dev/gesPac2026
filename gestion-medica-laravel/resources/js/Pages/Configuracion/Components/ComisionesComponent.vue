<template>
    <div class="q-pa-md">
        <StandardTable
            title="Configuración de Comisiones"
            :columns="columns"
            :rows="comisiones"
            :loading="loading"
            :filter="filter"
            @request="loadComisiones"
            @new="openDialog(null)"
            @edit="openDialog"
            @delete="deleteComision"
        >
            <template v-slot:filters>
                <q-select
                    v-model="filtroProducto"
                    :options="productosOptions"
                    option-value="id"
                    option-label="nombre"
                    label="Producto"
                    outlined
                    dense
                    clearable
                    emit-value
                    map-options
                    use-input
                    input-debounce="0"
                    @filter="filterProductos"
                    @update:model-value="loadComisiones"
                    style="min-width: 200px"
                    class="q-mr-sm"
                />
                <q-select
                    v-model="filtroCargo"
                    :options="cargosOptions"
                    option-value="id"
                    option-label="nombre"
                    label="Cargo"
                    outlined
                    dense
                    clearable
                    emit-value
                    map-options
                    @update:model-value="loadComisiones"
                    style="min-width: 200px"
                    class="q-mr-sm"
                />
            </template>
            <template v-slot:body-cell-empleado="props">
                <q-td :props="props">
                    <div class="text-weight-bold">{{ props.row.empleado?.nombres }} {{ props.row.empleado?.apellidos }}</div>
                    <div class="text-caption text-indigo-7">
                        {{ props.row.empleado?.cargo?.nombre || props.row.empleado?.cargo_principal?.[0]?.nombre }}
                    </div>
                    <div class="text-caption text-grey">{{ props.row.empleado?.numero_documento }}</div>
                </q-td>
            </template>
            <template v-slot:body-cell-producto="props">
                <q-td :props="props">
                    <div>{{ props.row.producto?.nombre }}</div>
                    <div class="text-caption text-grey">{{ props.row.producto?.codigo }}</div>
                </q-td>
            </template>
            <template v-slot:body-cell-valor="props">
                <q-td :props="props">
                    <q-badge
                        :color="props.row.tipo_calculo === 'PORCENTAJE' ? 'blue' : 'green'"
                        outline
                    >
                        {{ props.row.tipo_calculo === 'PORCENTAJE' ? '%' : '$' }}
                        {{ formatValor(props.row) }}
                    </q-badge>
                </q-td>
            </template>
            <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                    <q-btn flat round dense color="primary" icon="edit" @click="openDialog(props.row)">
                        <q-tooltip>Editar</q-tooltip>
                    </q-btn>
                    <q-btn flat round dense color="negative" icon="delete" @click="deleteComision(props.row)">
                         <q-tooltip>Eliminar</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </StandardTable>

        <q-dialog v-model="dialog" persistent transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 500px" class="modern-dialog">
                <!-- HEADER -->
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon name="payments" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ editingId ? 'Editar Regla' : 'Nueva Comisión' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <!-- FORM -->
                <q-card-section class="q-pt-md">
                    <q-form @submit="saveComision" class="q-gutter-md">
                        
                        <q-select
                            v-model="form.empleado_id"
                            :options="empleadosOptions"
                            option-value="id"
                            option-label="fullName"
                            label="Empleado *"
                            outlined
                            dense
                            emit-value
                            map-options
                            use-input
                            input-debounce="0"
                            @filter="filterEmpleados"
                            :rules="[val => !!val || 'Requerido']"
                        >
                            <template v-slot:option="scope">
                                <q-item v-bind="scope.itemProps">
                                    <q-item-section>
                                        <q-item-label>{{ scope.opt.fullName }}</q-item-label>
                                        <q-item-label caption class="text-primary">
                                            {{ scope.opt.cargo?.nombre || scope.opt.cargo_principal?.[0]?.nombre }}
                                        </q-item-label>
                                        <q-item-label caption>{{ scope.opt.numero_documento }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </template>
                            <template v-slot:no-option>
                                <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
                            </template>
                        </q-select>

                        <q-select
                            v-model="form.producto_id"
                            :options="productosOptions"
                            option-value="id"
                            option-label="nombre"
                            label="Producto *"
                            outlined
                            dense
                            emit-value
                            map-options
                            use-input
                            input-debounce="0"
                            @filter="filterProductos"
                            :rules="[val => !!val || 'Requerido']"
                        >
                             <template v-slot:option="scope">
                                <q-item v-bind="scope.itemProps">
                                    <q-item-section>
                                        <q-item-label>{{ scope.opt.nombre }}</q-item-label>
                                        <q-item-label caption>Cod: {{ scope.opt.codigo }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </template>
                             <template v-slot:no-option>
                                <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
                            </template>
                        </q-select>

                        <div class="row q-col-gutter-sm">
                            <div class="col-12 col-sm-6">
                                <q-select
                                    v-model="form.tipo_calculo"
                                    :options="tiposCalculo"
                                    label="Tipo de Cálculo"
                                    outlined
                                    dense
                                    emit-value
                                    map-options
                                />
                            </div>
                            <div class="col-12 col-sm-6">
                                <q-input
                                    v-model.number="form.valor"
                                    label="Valor *"
                                    outlined
                                    dense
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    :prefix="form.tipo_calculo === 'FIJO' ? '$' : ''"
                                    :suffix="form.tipo_calculo === 'PORCENTAJE' ? '%' : ''"
                                    :rules="[val => val >= 0 || 'Inválido']"
                                />
                            </div>
                        </div>

                        <!-- Submit hidden -->
                        <button type="submit" style="display: none"></button>
                    </q-form>
                </q-card-section>

                <q-separator />

                <!-- FOOTER -->
                <q-card-section class="row justify-end q-pt-xs q-pb-sm">
                    <q-btn label="Cancelar" color="grey-7" flat v-close-popup class="q-mr-sm" />
                    <q-btn :label="editingId ? 'Actualizar' : 'Guardar'" color="primary" unelevated :loading="saving" @click="saveComision" />
                </q-card-section>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";
import StandardTable from "@/Components/Shared/StandardTable.vue";

const $q = useQuasar();

// State
const comisiones = ref([]);
const loading = ref(false);
const filter = ref("");
const filtroProducto = ref(null);
const filtroCargo = ref(null);
const dialog = ref(false);
const saving = ref(false);
const editingId = ref(null);

// Select Options Data
const allEmpleados = ref([]);
const empleadosOptions = ref([]);
const allProductos = ref([]);
const productosOptions = ref([]);
const cargosOptions = ref([]);

const tiposCalculo = [
    { label: 'Porcentaje (%)', value: 'PORCENTAJE' },
    { label: 'Monto Fijo ($)', value: 'FIJO' }
];

const form = ref({
    empleado_id: null,
    producto_id: null,
    tipo_calculo: 'PORCENTAJE',
    valor: 0
});

const columns = [
    { name: 'empleado', label: 'Empleado', align: 'left', field: row => row.empleado?.nombres, sortable: true },
    { name: 'producto', label: 'Producto', align: 'left', field: row => row.producto?.nombre, sortable: true },
    { name: 'tipo_calculo', label: 'Tipo', align: 'center', field: 'tipo_calculo', sortable: true },
    { name: 'valor', label: 'Valor Comisión', align: 'right', field: 'valor', sortable: true },
    { name: 'actions', label: 'Acciones', align: 'center' }
];

// Methods
const loadComisiones = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/comisiones-productos', {
            params: {
                search: filter.value,
                producto_id: filtroProducto.value,
                cargo_id: filtroCargo.value
            }
        });
        comisiones.value = response.data;
    } catch (error) {
        console.error("Error loading comisiones", error);
        $q.notify({ type: 'negative', message: 'Error al cargar comisiones' });
    } finally {
        loading.value = false;
    }
};

const loadEmpleados = async () => {
    try {
        const response = await axios.get('/api/empleados');
        const data = Array.isArray(response.data) ? response.data : response.data.data;
        allEmpleados.value = data.map(e => ({
            ...e,
            fullName: `${e.nombres} ${e.apellidos}`
        }));
        empleadosOptions.value = allEmpleados.value;
    } catch (e) { console.error(e); }
};

const loadProductos = async () => {
    try {
        const response = await axios.get('/api/productos');
        const data = Array.isArray(response.data) ? response.data : response.data.data;
        allProductos.value = data;
        productosOptions.value = allProductos.value;
    } catch (e) { console.error(e); }
};

const loadCargos = async () => {
    try {
        const response = await axios.get('/api/cargos');
        cargosOptions.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    } catch (e) { console.error(e); }
};

const filterEmpleados = (val, update) => {
    if (val === '') {
        update(() => { empleadosOptions.value = allEmpleados.value });
        return;
    }
    update(() => {
        const needle = val.toLowerCase();
        empleadosOptions.value = allEmpleados.value.filter(v => v.fullName.toLowerCase().indexOf(needle) > -1 || v.numero_documento.includes(needle));
    });
};

const filterProductos = (val, update) => {
    if (val === '') {
        update(() => { productosOptions.value = allProductos.value });
        return;
    }
    update(() => {
        const needle = val.toLowerCase();
        productosOptions.value = allProductos.value.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1 || v.codigo.toLowerCase().includes(needle));
    });
};

const openDialog = (row = null) => {
    if (row) {
        editingId.value = row.id;
        form.value = {
            empleado_id: row.empleado_id,
            producto_id: row.producto_id,
            tipo_calculo: row.tipo_calculo,
            valor: parseFloat(row.valor)
        };
    } else {
        editingId.value = null;
        form.value = {
            empleado_id: null,
            producto_id: null,
            tipo_calculo: 'PORCENTAJE',
            valor: 0
        };
    }
    dialog.value = true;
};

const saveComision = async () => {
    saving.value = true;
    try {
        if (editingId.value) {
            await axios.put(`/api/comisiones-productos/${editingId.value}`, form.value);
            $q.notify({ type: 'positive', message: 'Actualizado correctamente' });
        } else {
            await axios.post('/api/comisiones-productos', form.value);
            $q.notify({ type: 'positive', message: 'Creado correctamente' });
        }
        dialog.value = false;
        loadComisiones();
    } catch (error) {
        console.error(error);
        const msg = error.response?.data?.message || "Error al guardar";
        $q.notify({ type: 'negative', message: msg });
         if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(err => {
                 $q.notify({ type: 'negative', message: err[0], timeout: 3000 });
            });
        }
    } finally {
        saving.value = false;
    }
};

const deleteComision = (row) => {
    $q.dialog({
        title: 'Confirmar Eliminación',
        message: '¿Está seguro de que desea eliminar esta regla de comisión? Esta acción no se puede deshacer.',
        class: 'modern-dialog',
        ok: {
            label: 'Eliminar',
            color: 'negative',
            unelevated: true
        },
        cancel: {
            label: 'Cancelar',
            color: 'grey-7',
            flat: true
        },
        persistent: true
    }).onOk(async () => {
        try {
            await axios.delete(`/api/comisiones-productos/${row.id}`);
            $q.notify({ type: 'positive', message: 'Eliminado correctamente' });
            loadComisiones();
        } catch (error) {
            $q.notify({ type: 'negative', message: 'Error al eliminar' });
        }
    });
};

const formatValor = (row) => {
    if (row.tipo_calculo === 'PORCENTAJE') return row.valor;
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(row.valor).replace('COP', '').trim();
};

onMounted(() => {
    loadComisiones();
    loadEmpleados();
    loadProductos();
    loadCargos();
});
</script>

<style scoped>
.modern-dialog {
    border-radius: 8px;
    overflow: hidden;
}
:deep(.q-table tbody td) {
    height: 35px !important;
    padding: 4px 8px !important;
}
</style>
