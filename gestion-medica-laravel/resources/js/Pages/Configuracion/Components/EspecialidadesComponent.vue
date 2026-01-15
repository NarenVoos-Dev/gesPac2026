<template>
    <div class="especialidades-container">
        <!-- HEADER CON FILTROS Y BOTÓN -->
        <div class="row items-center q-mb-sm q-gutter-sm">
            <!-- Título -->
            <div class="col-12 col-md-auto">
                <div class="text-h6 text-grey-9">Especialidades</div>
                <div class="text-caption text-grey-6">Gestión de especialidades médicas</div>
            </div>

            <q-space class="gt-sm" />

            <!-- Filtros compactos -->
            <div class="col-12 col-md-auto">
                <q-input
                    v-model="search"
                    placeholder="Buscar..."
                    outlined
                    dense
                    clearable
                    style="min-width: 200px"
                    @update:model-value="loadEspecialidades"
                >
                    <template v-slot:prepend>
                        <q-icon name="search" size="xs" />
                    </template>
                </q-input>
            </div>

            <div class="col-12 col-md-auto">
                <q-select
                    v-model="filtroEstado"
                    :options="opcionesEstado"
                    outlined
                    dense
                    emit-value
                    map-options
                    style="min-width: 120px"
                    @update:model-value="loadEspecialidades"
                />
            </div>

            <!-- Botón Nuevo -->
            <div class="col-12 col-md-auto">
                <q-btn
                    color="primary"
                    icon="add"
                    label="Nuevo"
                    @click="openDialog()"
                    unelevated
                    no-caps
                />
            </div>
        </div>

        <!-- TABLA -->
        <q-table
            :rows="especialidades"
            :columns="columns"
            row-key="id"
            :loading="loading"
            flat
            bordered
            dense
            :rows-per-page-options="[10, 25, 50]"
            class="modern-table"
        >
            <!-- Estado -->
            <template v-slot:body-cell-is_active="props">
                <q-td :props="props">
                    <q-badge
                        :color="props.row.is_active ? 'positive' : 'negative'"
                        :label="props.row.is_active ? 'Activo' : 'Inactivo'"
                    />
                </q-td>
            </template>

            <!-- Acciones -->
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

        <!-- DIALOG CREAR/EDITAR -->
        <q-dialog v-model="dialog" persistent transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 400px; max-width: 600px" class="modern-dialog">
                <!-- HEADER -->
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon name="medical_services" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ form.id ? 'Editar' : 'Nueva' }} Especialidad</div>
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
                            hint="Ej: CARDIO, PEDIATRIA, NEUROLOGIA"
                            :rules="[val => !!val || 'El código es requerido']"
                            outlined
                            dense
                            maxlength="50"
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
                            hint="Ej: Cardiología, Pediatría, Neurología"
                            :rules="[val => !!val || 'El nombre es requerido']"
                            outlined
                            dense
                        >
                            <template v-slot:prepend>
                                <q-icon name="medical_services" color="primary" size="xs" />
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
                        >
                            <template v-slot:prepend>
                                <q-icon name="description" color="primary" size="xs" />
                            </template>
                        </q-input>
                    </q-form>
                </q-card-section>

                <q-separator />

                <!-- FOOTER CON BOTONES -->
                <q-card-section class="row justify-end q-pt-xs q-pb-sm">
                    <q-btn
                        label="Cancelar"
                        color="grey-7"
                        flat
                        v-close-popup
                        class="q-mr-sm"
                    />
                    <q-btn
                        label="Guardar"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="save"
                    />
                </q-card-section>
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
const especialidades = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialog = ref(false);
const search = ref('');
const filtroEstado = ref('todos');

// Opciones de filtro
const opcionesEstado = [
    { label: 'Todos', value: 'todos' },
    { label: 'Activos', value: 'activos' },
    { label: 'Inactivos', value: 'inactivos' },
];

// Formulario
const form = ref({
    id: null,
    codigo: '',
    nombre: '',
    descripcion: '',
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
    },
    {
        name: 'acciones',
        label: 'Acciones',
        align: 'center',
    },
];

// Métodos
const loadEspecialidades = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/especialidades', {
            params: {
                search: search.value,
                estado: filtroEstado.value,
            },
        });
        especialidades.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar las especialidades',
            color: 'negative',
            textColor: 'white',
        });
    } finally {
        loading.value = false;
    }
};

const openDialog = (especialidad = null) => {
    if (especialidad) {
        form.value = { ...especialidad };
    } else {
        form.value = {
            id: null,
            codigo: '',
            nombre: '',
            descripcion: '',
        };
    }
    dialog.value = true;
};

const save = async () => {
    saving.value = true;
    try {
        if (form.value.id) {
            // Actualizar
            await axios.put(`/api/especialidades/${form.value.id}`, form.value);
            $q.notify({
                type: 'positive',
                message: 'Especialidad actualizada exitosamente',
                color: 'positive',
                textColor: 'white',
            });
        } else {
            // Crear
            await axios.post('/api/especialidades', form.value);
            $q.notify({
                type: 'positive',
                message: 'Especialidad creada exitosamente',
                color: 'positive',
                textColor: 'white',
            });
        }
        dialog.value = false;
        loadEspecialidades();
    } catch (error) {
        const message = error.response?.data?.message || 'Error al guardar la especialidad';
        $q.notify({
            type: 'negative',
            message: message,
            color: 'negative',
            textColor: 'white',
        });
    } finally {
        saving.value = false;
    }
};

const toggleStatus = async (especialidad) => {
    const action = especialidad.is_active ? 'desactivar' : 'activar';
    
    $q.dialog({
        title: 'Confirmar',
        message: `¿Está seguro de ${action} esta especialidad?`,
        cancel: true,
        persistent: true,
    }).onOk(async () => {
        try {
            if (especialidad.is_active) {
                await axios.delete(`/api/especialidades/${especialidad.id}`);
            } else {
                await axios.patch(`/api/especialidades/${especialidad.id}/restore`);
            }
            $q.notify({
                type: 'positive',
                message: `Especialidad ${action === 'desactivar' ? 'desactivada' : 'activada'} exitosamente`,
                color: 'positive',
                textColor: 'white',
            });
            loadEspecialidades();
        } catch (error) {
            $q.notify({
                type: 'negative',
                message: `Error al ${action} la especialidad`,
                color: 'negative',
                textColor: 'white',
            });
        }
    });
};

// Lifecycle
onMounted(() => {
    loadEspecialidades();
});
</script>

<style scoped>
.especialidades-container {
    /* Estilos consistentes */
}

.modern-table {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Tabla compacta */
:deep(.q-table) {
    font-size: 13px;
}

:deep(.q-table th) {
    font-weight: 600;
    background-color: #f5f5f5;
}

:deep(.q-table tbody td) {
    padding: 8px 12px;
}

:deep(.q-table thead tr),
:deep(.q-table tbody tr) {
    height: 40px;
}

/* Dialog moderno */
.modern-dialog {
    border-radius: 8px;
    overflow: hidden;
}
</style>
