<template>
    <div class="tipos-documento-container">
        <!-- HEADER CON BOTÓN NUEVO -->
        <div class="row items-center justify-between q-mb-sm">
            <div>
                <div class="text-h6 text-grey-9">Tipos de Documento</div>
                <div class="text-caption text-grey-6">Gestiona los tipos de documentos de identificación</div>
            </div>
            <q-btn
                color="primary"
                icon="add"
                label="Nuevo Tipo"
                @click="openDialog()"
                unelevated
            />
        </div>

        <!-- TABLA DE TIPOS DE DOCUMENTO -->
        <q-table
            :rows="tiposDocumento"
            :columns="columns"
            row-key="id"
            :loading="loading"
            flat
            bordered
            dense
            :rows-per-page-options="[10, 25, 50]"
            class="modern-table"
        >
            <!-- COLUMNA: Estado -->
            <template v-slot:body-cell-is_active="props">
                <q-td :props="props">
                    <q-badge
                        :color="props.row.is_active ? 'positive' : 'negative'"
                        :label="props.row.is_active ? 'Activo' : 'Inactivo'"
                    />
                </q-td>
            </template>

            <!-- COLUMNA: Acciones -->
            <template v-slot:body-cell-acciones="props">
                <q-td :props="props">
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="edit"
                        color="primary"
                        @click="openDialog(props.row)"
                    >
                        <q-tooltip>Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        :icon="props.row.is_active ? 'block' : 'check_circle'"
                        :color="props.row.is_active ? 'negative' : 'positive'"
                        @click="toggleStatus(props.row)"
                    >
                        <q-tooltip>{{ props.row.is_active ? 'Desactivar' : 'Activar' }}</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </q-table>

        <!-- DIALOG PARA CREAR/EDITAR -->
        <q-dialog v-model="dialog" persistent transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 400px; max-width: 600px" class="modern-dialog">
                <!-- HEADER -->
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon name="badge" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ form.id ? 'Editar' : 'Nuevo' }} Tipo de Documento</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <!-- FORMULARIO -->
                <q-card-section class="q-pt-xs">
                    <q-form @submit="save" class="q-gutter-xs">
                        <!-- Código -->
                        <q-input
                            v-model="form.codigo"
                            label="Código *"
                            hint="Ej: CC, TI, CE, PA"
                            :rules="[val => !!val || 'El código es requerido']"
                            outlined
                            dense
                            maxlength="10"
                            counter
                        >
                            <template v-slot:prepend>
                                <q-icon name="tag" color="primary" size="xs" />
                            </template>
                        </q-input>

                        <!-- Nombre -->
                        <q-input
                            v-model="form.nombre"
                            label="Nombre *"
                            hint="Ej: Cédula de Ciudadanía"
                            :rules="[val => !!val || 'El nombre es requerido']"
                            outlined
                            dense
                        >
                            <template v-slot:prepend>
                                <q-icon name="description" color="primary" size="xs" />
                            </template>
                        </q-input>

                        <!-- Descripción -->
                        <q-input
                            v-model="form.descripcion"
                            label="Descripción"
                            type="textarea"
                            outlined
                            dense
                            rows="3"
                            hint="Información adicional sobre el tipo de documento"
                        >
                            <template v-slot:prepend>
                                <q-icon name="notes" color="primary" size="xs" />
                            </template>
                        </q-input>

                        <!-- Estado -->
                        <div class="row items-center q-mt-sm">
                            <q-toggle
                                v-model="form.is_active"
                                label="Estado activo"
                                color="positive"
                                size="md"
                            />
                            <q-space />
                            <q-chip
                                :color="form.is_active ? 'positive' : 'negative'"
                                text-color="white"
                                size="sm"
                            >
                                {{ form.is_active ? 'Activo' : 'Inactivo' }}
                            </q-chip>
                        </div>
                    </q-form>
                </q-card-section>

                <q-separator />

                <!-- FOOTER CON BOTONES -->
                <q-card-actions align="right" class="q-pa-md">
                    <q-btn
                        
                        label="Cancelar"
                        color="grey-7"
                        flat
                        v-close-popup
                        icon="close"
                    />
                    <q-btn
                        label="Guardar"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="save"
                        icon="save"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

// Estado
const tiposDocumento = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialog = ref(false);
const form = ref({
    id: null,
    codigo: '',
    nombre: '',
    descripcion: '',
    is_active: true,
});

// Columnas de la tabla
const columns = [
    {
        name: 'codigo',
        label: 'Código',
        align: 'left',
        field: 'codigo',
        sortable: true,
    },
    {
        name: 'nombre',
        label: 'Nombre',
        align: 'left',
        field: 'nombre',
        sortable: true,
    },
    {
        name: 'descripcion',
        label: 'Descripción',
        align: 'left',
        field: 'descripcion',
    },
    {
        name: 'is_active',
        label: 'Estado',
        align: 'center',
        field: 'is_active',
        sortable: true,
    },
    {
        name: 'acciones',
        label: 'Acciones',
        align: 'center',
    },
];

// Cargar tipos de documento
const loadTiposDocumento = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/tipos-documento');
        tiposDocumento.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar tipos de documento',
            position: 'top',
        });
    } finally {
        loading.value = false;
    }
};

// Abrir dialog
const openDialog = (tipo = null) => {
    if (tipo) {
        form.value = { ...tipo };
    } else {
        form.value = {
            id: null,
            codigo: '',
            nombre: '',
            descripcion: '',
            is_active: true,
        };
    }
    dialog.value = true;
};

// Guardar
const save = async () => {
    saving.value = true;
    try {
        if (form.value.id) {
            // Actualizar
            await axios.put(`/api/tipos-documento/${form.value.id}`, form.value);
            $q.notify({
                type: 'positive',
                message: 'Tipo de documento actualizado correctamente',
                position: 'top',
            });
        } else {
            // Crear
            await axios.post('/api/tipos-documento', form.value);
            $q.notify({
                type: 'positive',
                message: 'Tipo de documento creado correctamente',
                position: 'top',
            });
        }
        dialog.value = false;
        loadTiposDocumento();
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: error.response?.data?.message || 'Error al guardar',
            position: 'top',
        });
    } finally {
        saving.value = false;
    }
};

// Cambiar estado con confirmación
const toggleStatus = async (tipo) => {
    // Si está activo, pedir confirmación para desactivar
    if (tipo.is_active) {
        $q.dialog({
            title: 'Confirmar desactivación',
            message: `¿Está seguro que desea desactivar el tipo de documento "${tipo.nombre}"? Los pacientes con este tipo de documento no podrán ser creados hasta que lo reactive.`,
            cancel: {
                label: 'Cancelar',
                color: 'grey-7',
                flat: true,
            },
            ok: {
                label: 'Desactivar',
                color: 'negative',
                unelevated: true,
            },
            persistent: true,
        }).onOk(async () => {
            try {
                await axios.patch(`/api/tipos-documento/${tipo.id}/toggle-status`);
                $q.notify({
                    type: 'positive',
                    message: 'Tipo de documento desactivado correctamente',
                    position: 'top',
                });
                loadTiposDocumento();
            } catch (error) {
                $q.notify({
                    type: 'negative',
                    message: 'Error al cambiar estado',
                    position: 'top',
                });
            }
        });
    } else {
        // Si está inactivo, activar directamente
        try {
            await axios.patch(`/api/tipos-documento/${tipo.id}/toggle-status`);
            $q.notify({
                type: 'positive',
                message: 'Tipo de documento activado correctamente',
                position: 'top',
            });
            loadTiposDocumento();
        } catch (error) {
            $q.notify({
                type: 'negative',
                message: 'Error al cambiar estado',
                position: 'top',
            });
        }
    }
};

// Cargar al montar
onMounted(() => {
    loadTiposDocumento();
});
</script>

<style scoped>
.tipos-documento-container {
    padding: 8px;
}

/* Tabla moderna y compacta */
.modern-table {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

:deep(.q-table__top) {
    padding: 8px 12px;
}

:deep(.q-table thead tr) {
    background: #F8F9FA;
    height: 32px;
}

:deep(.q-table thead th) {
    font-weight: 600;
    color: #37474F;
    font-size: 12px;
    padding: 8px 12px;
}

:deep(.q-table tbody td) {
    font-size: 12px;
    padding: 6px 12px;
}

:deep(.q-table tbody tr) {
    height: 36px;
}

:deep(.q-table tbody tr:hover) {
    background: #F8F9FA;
}

/* Botones más compactos */
:deep(.q-btn) {
    min-height: 32px;
}

/* Dialog moderno */
.modern-dialog {
    border-radius: 12px;
    overflow: hidden;
}

.modern-dialog :deep(.q-card__section--vert) {
    padding: 16px 20px;
}

.modern-dialog :deep(.q-field__label) {
    font-size: 13px;
    font-weight: 500;
}

.modern-dialog :deep(.q-field__control) {
    font-size: 13px;
}
</style>
