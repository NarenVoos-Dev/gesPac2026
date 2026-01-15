<template>
    <div class="roles-container">
        <!-- TABLA REUTILIZABLE -->
        <StandardTable
            title="Roles del Sistema"
            subtitle="Gestiona roles y sus permisos"
            :rows="roles"
            :columns="columns"
            :loading="loading"
            row-key="id"
            :show-new-button="true"
            new-button-label="Nuevo Rol"
            @new="openCreateDialog"
        >
            <!-- COLUMNA: Nombre -->
            <template v-slot:body-cell-name="props">
                <q-td :props="props">
                    <div class="text-weight-medium">{{ props.row.name }}</div>
                    <div class="text-caption text-grey-6">{{ props.row.guard_name }}</div>
                </q-td>
            </template>

            <!-- COLUMNA: Usuarios -->
            <template v-slot:body-cell-users_count="props">
                <q-td :props="props">
                    <q-badge color="blue" :label="props.row.users_count" />
                </q-td>
            </template>

            <!-- COLUMNA: Permisos -->
            <template v-slot:body-cell-permissions_count="props">
                <q-td :props="props">
                    <q-badge color="green" :label="props.row.permissions_count" />
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
                        :disable="isSystemRole(props.row.name)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-primary text-body2" :offset="[10, 10]">Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="lock"
                        color="info"
                        @click="openPermissionsDialog(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-info text-body2" :offset="[10, 10]">Gestionar Permisos</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="delete"
                        color="negative"
                        @click="deleteRole(props.row)"
                        :disable="isSystemRole(props.row.name)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-negative text-body2" :offset="[10, 10]">Eliminar</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </StandardTable>

        <!-- DIALOG CREAR/EDITAR ROL -->
        <q-dialog v-model="dialogEdit" persistent>
            <q-card style="min-width: 450px">
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon :name="form.id ? 'edit' : 'add_circle'" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ form.id ? 'Editar Rol' : 'Nuevo Rol' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <q-input
                        v-model="form.name"
                        label="Nombre del Rol *"
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
                        @click="saveRole"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- DIALOG GESTIONAR PERMISOS -->
        <q-dialog v-model="dialogPermissions" persistent>
            <q-card style="min-width: 600px; max-width: 800px">
                <q-card-section class="row items-center q-pb-xs bg-info text-white">
                    <q-icon name="lock" size="24px" class="q-mr-sm" />
                    <div class="text-h6">Gestionar Permisos</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-md">
                        Rol: <strong>{{ selectedRole?.name }}</strong>
                    </div>

                    <!-- Búsqueda de permisos -->
                    <q-input
                        v-model="permissionSearch"
                        outlined
                        dense
                        placeholder="Buscar permisos..."
                        class="q-mb-md"
                    >
                        <template v-slot:prepend>
                            <q-icon name="search" />
                        </template>
                    </q-input>

                    <!-- Permisos agrupados por módulo -->
                    <div v-for="(perms, module) in filteredPermissionsByModule" :key="module" class="q-mb-md">
                        <div class="text-weight-bold text-grey-8 q-mb-sm text-capitalize">
                            {{ module }}
                        </div>
                        <div class="row q-gutter-sm">
                            <q-checkbox
                                v-for="permission in perms"
                                :key="permission.name"
                                v-model="selectedPermissions"
                                :val="permission.name"
                                :label="permission.name.split('.')[1]"
                                dense
                            />
                        </div>
                    </div>
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
                        label="Guardar Permisos"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="savePermissions"
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
const roles = ref([]);
const allPermissions = ref({});
const loading = ref(false);
const saving = ref(false);
const dialogEdit = ref(false);
const dialogPermissions = ref(false);
const selectedRole = ref(null);
const selectedPermissions = ref([]);
const permissionSearch = ref('');

// Formulario
const form = ref({
    id: null,
    name: '',
});

// Columnas
const columns = [
    {
        name: 'name',
        label: 'Rol',
        align: 'left',
        field: 'name',
        sortable: true,
    },
    {
        name: 'users_count',
        label: 'Usuarios',
        align: 'center',
    },
    {
        name: 'permissions_count',
        label: 'Permisos',
        align: 'center',
    },
    {
        name: 'acciones',
        label: 'Acciones',
        align: 'center',
    },
];

// Computed
const filteredPermissionsByModule = computed(() => {
    if (!permissionSearch.value) {
        return allPermissions.value;
    }

    const search = permissionSearch.value.toLowerCase();
    const filtered = {};

    Object.keys(allPermissions.value).forEach(module => {
        const perms = allPermissions.value[module].filter(p =>
            p.name.toLowerCase().includes(search)
        );
        if (perms.length > 0) {
            filtered[module] = perms;
        }
    });

    return filtered;
});

// Métodos
const loadRoles = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/roles');
        roles.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar los roles',
        });
    } finally {
        loading.value = false;
    }
};

const loadPermissions = async () => {
    try {
        const response = await axios.get('/api/permissions');
        allPermissions.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar los permisos',
        });
    }
};

const isSystemRole = (roleName) => {
    return ['super-admin', 'admin', 'agente', 'visitador', 'profesional'].includes(roleName);
};

const openCreateDialog = () => {
    form.value = {
        id: null,
        name: '',
    };
    dialogEdit.value = true;
};

const openEditDialog = (role) => {
    form.value = {
        id: role.id,
        name: role.name,
    };
    dialogEdit.value = true;
};

const openPermissionsDialog = async (role) => {
    selectedRole.value = role;
    selectedPermissions.value = [...role.permissions];
    permissionSearch.value = '';
    dialogPermissions.value = true;
};

const saveRole = async () => {
    if (!form.value.name) {
        $q.notify({
            type: 'negative',
            message: 'El nombre del rol es requerido',
        });
        return;
    }

    saving.value = true;
    try {
        if (form.value.id) {
            await axios.put(`/api/roles/${form.value.id}`, {
                name: form.value.name,
            });
            $q.notify({
                type: 'positive',
                message: 'Rol actualizado exitosamente',
            });
        } else {
            await axios.post('/api/roles', {
                name: form.value.name,
            });
            $q.notify({
                type: 'positive',
                message: 'Rol creado exitosamente',
            });
        }
        dialogEdit.value = false;
        loadRoles();
    } catch (error) {
        const message = error.response?.data?.message || 'Error al guardar el rol';
        $q.notify({
            type: 'negative',
            message: message,
        });
    } finally {
        saving.value = false;
    }
};

const savePermissions = async () => {
    saving.value = true;
    try {
        await axios.post(`/api/roles/${selectedRole.value.id}/sync-permissions`, {
            permissions: selectedPermissions.value,
        });
        $q.notify({
            type: 'positive',
            message: 'Permisos actualizados exitosamente',
        });
        dialogPermissions.value = false;
        loadRoles();
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al actualizar los permisos',
        });
    } finally {
        saving.value = false;
    }
};

const deleteRole = (role) => {
    $q.dialog({
        title: 'Confirmar',
        message: `¿Está seguro de eliminar el rol "${role.name}"?`,
        cancel: true,
        persistent: true,
    }).onOk(async () => {
        try {
            await axios.delete(`/api/roles/${role.id}`);
            $q.notify({
                type: 'positive',
                message: 'Rol eliminado exitosamente',
            });
            loadRoles();
        } catch (error) {
            const message = error.response?.data?.message || 'Error al eliminar el rol';
            $q.notify({
                type: 'negative',
                message: message,
            });
        }
    });
};

// Lifecycle
onMounted(() => {
    loadRoles();
    loadPermissions();
});
</script>

<style scoped>
.roles-container {
    /* Estilos consistentes */
}
</style>
