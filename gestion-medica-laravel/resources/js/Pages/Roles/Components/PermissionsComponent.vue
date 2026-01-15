<template>
    <div class="permissions-container">
        <!-- TABLA REUTILIZABLE -->
        <StandardTable
            title="Permisos del Sistema"
            subtitle="Gestiona permisos y asignaciones"
            :rows="permissionsFlat"
            :columns="columns"
            :loading="loading"
            row-key="id"
            :show-new-button="true"
            new-button-label="Nuevo Permiso"
            @new="openCreateDialog"
        >
            <!-- COLUMNA: Nombre -->
            <template v-slot:body-cell-name="props">
                <q-td :props="props">
                    <div class="text-weight-medium">{{ props.row.name }}</div>
                </q-td>
            </template>

            <!-- COLUMNA: Módulo -->
            <template v-slot:body-cell-module="props">
                <q-td :props="props">
                    <q-badge color="purple" :label="props.row.module" class="text-capitalize" />
                </q-td>
            </template>

            <!-- COLUMNA: Roles -->
            <template v-slot:body-cell-roles="props">
                <q-td :props="props">
                    <q-chip
                        v-for="role in props.row.roles"
                        :key="role"
                        size="sm"
                        color="primary"
                        text-color="white"
                        dense
                    >
                        {{ role }}
                    </q-chip>
                    <span v-if="props.row.roles.length === 0" class="text-grey-6">Sin roles</span>
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
                        @click="openEditDialog(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-primary text-body2" :offset="[10, 10]">Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="delete"
                        color="negative"
                        @click="deletePermission(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-negative text-body2" :offset="[10, 10]">Eliminar</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </StandardTable>

        <!-- DIALOG CREAR/EDITAR PERMISO -->
        <q-dialog v-model="dialogEdit" persistent>
            <q-card style="min-width: 450px">
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon :name="form.id ? 'edit' : 'add_circle'" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ form.id ? 'Editar Permiso' : 'Nuevo Permiso' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <q-input
                        v-model="form.name"
                        label="Nombre del Permiso *"
                        hint="Formato: modulo.accion (ej: pacientes.ver)"
                        :rules="[val => !!val || 'Requerido']"
                        outlined
                        dense
                    />
                </q-card-section>

                <q-separator />

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
                        @click="savePermission"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import StandardTable from '@/Components/Shared/StandardTable.vue';

const $q = useQuasar();

// Estado
const permissions = ref({});
const loading = ref(false);
const saving = ref(false);
const dialogEdit = ref(false);

// Formulario
const form = ref({
    id: null,
    name: '',
});

// Columnas
const columns = [
    {
        name: 'name',
        label: 'Permiso',
        align: 'left',
        field: 'name',
        sortable: true,
    },
    {
        name: 'module',
        label: 'Módulo',
        align: 'center',
        field: 'module',
        sortable: true,
    },
    {
        name: 'roles',
        label: 'Roles Asignados',
        align: 'left',
    },
    {
        name: 'acciones',
        label: 'Acciones',
        align: 'center',
    },
];

// Computed - Aplanar permisos agrupados
const permissionsFlat = computed(() => {
    const flat = [];
    Object.keys(permissions.value).forEach(module => {
        permissions.value[module].forEach(permission => {
            flat.push(permission);
        });
    });
    return flat;
});

// Métodos
const loadPermissions = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/permissions');
        permissions.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar los permisos',
        });
    } finally {
        loading.value = false;
    }
};

const openCreateDialog = () => {
    form.value = {
        id: null,
        name: '',
    };
    dialogEdit.value = true;
};

const openEditDialog = (permission) => {
    form.value = {
        id: permission.id,
        name: permission.name,
    };
    dialogEdit.value = true;
};

const savePermission = async () => {
    if (!form.value.name) {
        $q.notify({
            type: 'negative',
            message: 'El nombre del permiso es requerido',
        });
        return;
    }

    saving.value = true;
    try {
        if (form.value.id) {
            await axios.put(`/api/permissions/${form.value.id}`, {
                name: form.value.name,
            });
            $q.notify({
                type: 'positive',
                message: 'Permiso actualizado exitosamente',
            });
        } else {
            await axios.post('/api/permissions', {
                name: form.value.name,
            });
            $q.notify({
                type: 'positive',
                message: 'Permiso creado exitosamente',
            });
        }
        dialogEdit.value = false;
        loadPermissions();
    } catch (error) {
        const message = error.response?.data?.message || 'Error al guardar el permiso';
        $q.notify({
            type: 'negative',
            message: message,
        });
    } finally {
        saving.value = false;
    }
};

const deletePermission = (permission) => {
    $q.dialog({
        title: 'Confirmar',
        message: `¿Está seguro de eliminar el permiso "${permission.name}"?`,
        cancel: true,
        persistent: true,
    }).onOk(async () => {
        try {
            await axios.delete(`/api/permissions/${permission.id}`);
            $q.notify({
                type: 'positive',
                message: 'Permiso eliminado exitosamente',
            });
            loadPermissions();
        } catch (error) {
            const message = error.response?.data?.message || 'Error al eliminar el permiso';
            $q.notify({
                type: 'negative',
                message: message,
            });
        }
    });
};

// Lifecycle
onMounted(() => {
    loadPermissions();
});
</script>

<style scoped>
.permissions-container {
    /* Estilos consistentes */
}
</style>
