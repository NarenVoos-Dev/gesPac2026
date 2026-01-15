<template>
    <div class="usuarios-container">
        <!-- TABLA REUTILIZABLE CON FILTROS INTEGRADOS -->
        <StandardTable
            title="Usuarios del Sistema"
            subtitle="Gestiona usuarios y roles"
            :rows="usuarios"
            :columns="columns"
            :loading="loading"
            row-key="id"
            :show-new-button="true"
            new-button-label="Nuevo Usuario"
            @new="openCreateDialog"
        >
            <!-- SLOT: Top (filtros) -->
            <template v-slot:top-right>
                <div class="row items-center q-gutter-sm">
                    <!-- Búsqueda -->
                    <q-input
                        v-model="search"
                        outlined
                        dense
                        placeholder="Buscar por nombre o email..."
                        style="min-width: 250px"
                        @update:model-value="loadUsuarios"
                    >
                        <template v-slot:prepend>
                            <q-icon name="search" />
                        </template>
                        <template v-slot:append v-if="search">
                            <q-icon name="close" class="cursor-pointer" @click="search = ''; loadUsuarios()" />
                        </template>
                    </q-input>

                    <!-- Filtro de rol -->
                    <q-select
                        v-model="filtroRol"
                        :options="rolesOptions"
                        outlined
                        dense
                        style="min-width: 180px"
                        @update:model-value="loadUsuarios"
                    />

                    <!-- Filtro de estado -->
                    <q-select
                        v-model="filtroEstado"
                        :options="opcionesEstado"
                        outlined
                        dense
                        style="min-width: 150px"
                        @update:model-value="loadUsuarios"
                    />
                </div>
            </template>
            <!-- COLUMNA: Nombre -->
            <template v-slot:body-cell-nombre="props">
                <q-td :props="props">
                    <div class="text-weight-medium">{{ props.row.name }}</div>
                    <div class="text-caption text-grey-6">{{ props.row.email }}</div>
                </q-td>
            </template>

            <!-- COLUMNA: Empleado -->
            <template v-slot:body-cell-empleado="props">
                <q-td :props="props">
                    <div v-if="props.row.empleado">
                        {{ props.row.empleado.nombres }} {{ props.row.empleado.apellidos }}
                    </div>
                    <div v-else class="text-grey-6">Sin empleado</div>
                </q-td>
            </template>

            <!-- COLUMNA: Rol -->
            <template v-slot:body-cell-rol="props">
                <q-td :props="props">
                    <q-badge
                        v-if="props.row.roles && props.row.roles.length > 0"
                        :color="getRolColor(props.row.roles[0].name)"
                        :label="getRolLabel(props.row.roles[0].name)"
                    />
                    <span v-else class="text-grey-6">Sin rol</span>
                </q-td>
            </template>

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
                        @click="openEditDialog(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-primary text-body2" :offset="[10, 10]">Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="admin_panel_settings"
                        color="info"
                        @click="openRoleDialog(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-info text-body2" :offset="[10, 10]">Cambiar Rol</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="lock_reset"
                        color="warning"
                        @click="resetPassword(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-warning text-body2" :offset="[10, 10]">Cambiar Contraseña</q-tooltip>
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
                        <q-tooltip transition-show="rotate" transition-hide="rotate" :class="props.row.is_active ? 'bg-negative' : 'bg-positive'" class="text-body2" :offset="[10, 10]">{{ props.row.is_active ? 'Desactivar' : 'Activar' }}</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </StandardTable>

        <!-- DIALOG CREAR/EDITAR USUARIO -->
        <q-dialog v-model="dialogEdit" persistent>
            <q-card style="min-width: 500px">
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon :name="form.id ? 'edit' : 'person_add'" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ form.id ? 'Editar Usuario' : 'Nuevo Usuario' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <q-form @submit="saveUser">
                        <q-input
                            v-model="form.name"
                            label="Nombre *"
                            :rules="[val => !!val || 'Requerido']"
                            outlined
                            dense
                            class="q-mb-sm"
                        />
                        <q-input
                            v-model="form.email"
                            label="Email *"
                            type="email"
                            :rules="[val => !!val || 'Requerido']"
                            outlined
                            dense
                            class="q-mb-sm"
                        />
                        
                        <!-- Campos adicionales solo para creación -->
                        <template v-if="!form.id">
                            <q-select
                                v-model="form.rol"
                                :options="rolesSelectOptions"
                                label="Rol *"
                                outlined
                                dense
                                emit-value
                                map-options
                                :rules="[val => !!val || 'Requerido']"
                                class="q-mb-sm"
                            />
                            
                            <q-input
                                v-model="form.password"
                                label="Contraseña *"
                                :type="showPassword ? 'text' : 'password'"
                                :rules="[
                                    val => !!val || 'Requerido',
                                    val => val.length >= 8 || 'Mínimo 8 caracteres'
                                ]"
                                outlined
                                dense
                                class="q-mb-sm"
                            >
                                <template v-slot:append>
                                    <q-icon
                                        :name="showPassword ? 'visibility_off' : 'visibility'"
                                        class="cursor-pointer"
                                        @click="showPassword = !showPassword"
                                    />
                                </template>
                            </q-input>
                            
                            <q-input
                                v-model="form.password_confirmation"
                                label="Confirmar Contraseña *"
                                :type="showPassword ? 'text' : 'password'"
                                :rules="[
                                    val => !!val || 'Requerido',
                                    val => val === form.password || 'Las contraseñas no coinciden'
                                ]"
                                outlined
                                dense
                            />
                        </template>
                    </q-form>
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
                        @click="saveUser"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- DIALOG CAMBIAR ROL -->
        <q-dialog v-model="dialogRole" persistent>
            <q-card style="min-width: 400px">
                <q-card-section class="row items-center q-pb-xs bg-info text-white">
                    <q-icon name="admin_panel_settings" size="24px" class="q-mr-sm" />
                    <div class="text-h6">Cambiar Rol</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-sm">
                        {{ selectedUser?.name }}
                    </div>
                    <q-select
                        v-model="form.rol"
                        :options="rolesSelectOptions"
                        label="Rol *"
                        outlined
                        dense
                        emit-value
                        map-options
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

        <!-- DIALOG CAMBIAR CONTRASEÑA -->
        <q-dialog v-model="dialogPassword" persistent>
            <q-card style="min-width: 450px">
                <q-card-section class="row items-center q-pb-xs bg-warning text-white">
                    <q-icon name="lock_reset" size="24px" class="q-mr-sm" />
                    <div class="text-h6">Cambiar Contraseña</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-md">
                        {{ selectedUser?.name }}
                    </div>
                    <q-form @submit="savePassword">
                        <q-input
                            v-model="form.password"
                            label="Nueva Contraseña *"
                            :type="showPassword ? 'text' : 'password'"
                            :rules="[
                                val => !!val || 'Requerido',
                                val => val.length >= 8 || 'Mínimo 8 caracteres'
                            ]"
                            outlined
                            dense
                            class="q-mb-sm"
                        >
                            <template v-slot:append>
                                <q-icon
                                    :name="showPassword ? 'visibility_off' : 'visibility'"
                                    class="cursor-pointer"
                                    @click="showPassword = !showPassword"
                                />
                            </template>
                        </q-input>
                        <q-input
                            v-model="form.password_confirmation"
                            label="Confirmar Contraseña *"
                            :type="showPassword ? 'text' : 'password'"
                            :rules="[
                                val => !!val || 'Requerido',
                                val => val === form.password || 'Las contraseñas no coinciden'
                            ]"
                            outlined
                            dense
                        />
                    </q-form>
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
                        @click="savePassword"
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
import StandardTable from '@/Components/Shared/StandardTable.vue';

const $q = useQuasar();

// Estado
const usuarios = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialogEdit = ref(false);
const dialogRole = ref(false);
const dialogPassword = ref(false);
const showPassword = ref(false);
const search = ref('');
const filtroRol = ref({ label: 'Todos los roles', value: null });
const filtroEstado = ref('todos');
const selectedUser = ref(null);

// Opciones
const opcionesEstado = [
    { label: 'Todos', value: 'todos' },
    { label: 'Activos', value: 'activos' },
    { label: 'Inactivos', value: 'inactivos' },
];

const rolesOptions = [
    { label: 'Todos los roles', value: null },
    { label: 'Administrador', value: 'admin' },
    { label: 'Agente', value: 'agente' },
    { label: 'Visitador Médico', value: 'visitador' },
    { label: 'Profesional de Salud', value: 'profesional' },
];

const rolesSelectOptions = [
    { label: 'Administrador', value: 'admin' },
    { label: 'Agente', value: 'agente' },
    { label: 'Visitador Médico', value: 'visitador' },
    { label: 'Profesional de Salud', value: 'profesional' },
];

// Formulario
const form = ref({
    id: null,
    name: '',
    email: '',
    rol: null,
    password: '',
    password_confirmation: '',
});

// Columnas
const columns = [
    {
        name: 'nombre',
        label: 'Usuario',
        align: 'left',
        field: 'name',
        sortable: true,
    },
    {
        name: 'empleado',
        label: 'Empleado',
        align: 'left',
    },
    {
        name: 'rol',
        label: 'Rol',
        align: 'center',
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
const loadUsuarios = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/usuarios', {
            params: {
                search: search.value,
                rol: filtroRol.value.value,
                estado: filtroEstado.value,
            },
        });
        usuarios.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar los usuarios',
        });
    } finally {
        loading.value = false;
    }
};

const openCreateDialog = () => {
    selectedUser.value = null;
    form.value = {
        id: null,
        name: '',
        email: '',
        rol: null,
        password: '',
        password_confirmation: '',
    };
    showPassword.value = false;
    dialogEdit.value = true;
};

const openEditDialog = (usuario) => {
    selectedUser.value = usuario;
    form.value = {
        id: usuario.id,
        name: usuario.name,
        email: usuario.email,
    };
    dialogEdit.value = true;
};

const openRoleDialog = (usuario) => {
    selectedUser.value = usuario;
    form.value = {
        id: usuario.id,
        rol: usuario.roles?.[0]?.name || null,
    };
    dialogRole.value = true;
};

const saveUser = async () => {
    saving.value = true;
    try {
        if (form.value.id) {
            // Actualizar (solo nombre y email)
            await axios.put(`/api/usuarios/${form.value.id}`, {
                name: form.value.name,
                email: form.value.email,
            });
            $q.notify({
                type: 'positive',
                message: 'Usuario actualizado exitosamente',
            });
        } else {
            // Crear nuevo usuario
            // Validar contraseñas
            if (form.value.password !== form.value.password_confirmation) {
                $q.notify({
                    type: 'negative',
                    message: 'Las contraseñas no coinciden',
                });
                saving.value = false;
                return;
            }

            await axios.post('/api/usuarios', {
                name: form.value.name,
                email: form.value.email,
                password: form.value.password,
                rol: form.value.rol,
            });
            $q.notify({
                type: 'positive',
                message: 'Usuario creado exitosamente',
            });
        }
        dialogEdit.value = false;
        loadUsuarios();
    } catch (error) {
        const message = error.response?.data?.message || 'Error al guardar el usuario';
        $q.notify({
            type: 'negative',
            message: message,
        });
    } finally {
        saving.value = false;
    }
};

const saveRole = async () => {
    saving.value = true;
    try {
        await axios.patch(`/api/usuarios/${form.value.id}/rol`, {
            rol: form.value.rol,
        });
        $q.notify({
            type: 'positive',
            message: 'Rol actualizado exitosamente',
        });
        dialogRole.value = false;
        loadUsuarios();
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al actualizar el rol',
        });
    } finally {
        saving.value = false;
    }
};

const toggleStatus = async (usuario) => {
    const action = usuario.is_active ? 'desactivar' : 'activar';
    
    $q.dialog({
        title: 'Confirmar',
        message: `¿Está seguro de ${action} este usuario?`,
        cancel: true,
        persistent: true,
    }).onOk(async () => {
        try {
            await axios.patch(`/api/usuarios/${usuario.id}/toggle-status`);
            $q.notify({
                type: 'positive',
                message: `Usuario ${action === 'desactivar' ? 'desactivado' : 'activado'} exitosamente`,
            });
            loadUsuarios();
        } catch (error) {
            const message = error.response?.data?.message || `Error al ${action} el usuario`;
            $q.notify({
                type: 'negative',
                message: message,
            });
        }
    });
};

const resetPassword = (usuario) => {
    selectedUser.value = usuario;
    form.value = {
        id: usuario.id,
        password: '',
        password_confirmation: '',
    };
    showPassword.value = false;
    dialogPassword.value = true;
};

const savePassword = async () => {
    // Validar que las contraseñas coincidan
    if (form.value.password !== form.value.password_confirmation) {
        $q.notify({
            type: 'negative',
            message: 'Las contraseñas no coinciden',
        });
        return;
    }

    if (form.value.password.length < 8) {
        $q.notify({
            type: 'negative',
            message: 'La contraseña debe tener al menos 8 caracteres',
        });
        return;
    }

    saving.value = true;
    try {
        await axios.post(`/api/usuarios/${form.value.id}/reset-password`, {
            password: form.value.password,
        });
        $q.notify({
            type: 'positive',
            message: 'Contraseña cambiada exitosamente',
        });
        dialogPassword.value = false;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cambiar la contraseña',
        });
    } finally {
        saving.value = false;
    }
};

const getRolColor = (rol) => {
    const colors = {
        admin: 'red',
        agente: 'blue',
        visitador: 'purple',
        profesional: 'green',
    };
    return colors[rol] || 'grey';
};

const getRolLabel = (rol) => {
    const labels = {
        admin: 'Administrador',
        agente: 'Agente',
        visitador: 'Visitador Médico',
        profesional: 'Profesional',
    };
    return labels[rol] || rol;
};

// Lifecycle
onMounted(() => {
    loadUsuarios();
});
</script>

<style scoped>
.usuarios-container {
    /* Estilos consistentes */
}
</style>
