<template>
    <div class="empleados-container">
        <!-- HEADER CON FILTROS Y BOTÓN -->
        <div class="row items-center q-mb-sm q-gutter-sm">
            <!-- Título -->
            <div class="col-12 col-md-auto">
                <div class="text-h6 text-grey-9">Empleados</div>
                <div class="text-caption text-grey-6">Gestión de personal</div>
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
                    @update:model-value="loadEmpleados"
                >
                    <template v-slot:prepend>
                        <q-icon name="search" size="xs" />
                    </template>
                </q-input>
            </div>

            <div class="col-12 col-md-auto">
                <q-select
                    v-model="filtroCargo"
                    :options="cargosOptions"
                    outlined
                    dense
                    emit-value
                    map-options
                    style="min-width: 150px"
                    @update:model-value="loadEmpleados"
                />
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
                    @update:model-value="loadEmpleados"
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
            :rows="empleados"
            :columns="columns"
            row-key="id"
            :loading="loading"
            flat
            bordered
            dense
            :rows-per-page-options="[10, 25, 50]"
            class="modern-table"
        >
            <!-- Cargo -->
            <template v-slot:body-cell-cargo="props">
                <q-td :props="props">
                    <q-badge
                        v-if="props.row.cargo_principal && props.row.cargo_principal.length > 0"
                        color="primary"
                        :label="props.row.cargo_principal[0].nombre"
                        outline
                    />
                    <span v-else class="text-grey-6">Sin cargo</span>
                </q-td>
            </template>

            <!-- Usuario -->
            <template v-slot:body-cell-usuario="props">
                <q-td :props="props">
                    <q-icon
                        v-if="props.row.user"
                        name="check_circle"
                        color="positive"
                        size="sm"
                    >
                        <q-tooltip>Tiene usuario</q-tooltip>
                    </q-icon>
                    <q-icon
                        v-else
                        name="cancel"
                        color="grey"
                        size="sm"
                    >
                        <q-tooltip>Sin usuario</q-tooltip>
                    </q-icon>
                </q-td>
            </template>

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
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-primary text-body2" :offset="[10, 10]">Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                        v-if="!props.row.user"
                        size="sm"
                        flat
                        dense
                        round
                        icon="person_add"
                        color="positive"
                        @click="openCrearUsuarioDialog(props.row)"
                    >
                        <q-tooltip transition-show="rotate" transition-hide="rotate" class="bg-positive text-body2" :offset="[10, 10]">Crear Usuario</q-tooltip>
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
        </q-table>

        <!-- DIALOG CREAR/EDITAR -->
        <q-dialog v-model="dialog" persistent transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 600px; max-width: 800px" class="modern-dialog">
                <!-- HEADER -->
                <q-card-section class="row items-center q-pb-xs bg-primary text-white">
                    <q-icon name="person" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ form.id ? 'Editar' : 'Nuevo' }} Empleado</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <!-- FORMULARIO -->
                <q-card-section class="q-pt-xs" style="max-height: 70vh; overflow-y: auto;">
                    <q-form @submit="save" class="q-gutter-xs">
                        <!-- SECCIÓN: Información Personal -->
                        <div class="text-subtitle2 text-primary q-mb-xs">Información Personal</div>
                        <div class="row q-col-gutter-sm">
                            <!-- Nombres -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.nombres"
                                    label="Nombres *"
                                    :rules="[val => !!val || 'Requerido']"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Apellidos -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.apellidos"
                                    label="Apellidos *"
                                    :rules="[val => !!val || 'Requerido']"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Tipo Documento -->
                            <div class="col-12 col-md-4">
                                <q-select
                                    v-model="form.tipo_documento_id"
                                    :options="tiposDocumento"
                                    option-value="id"
                                    option-label="nombre"
                                    emit-value
                                    map-options
                                    label="Tipo Documento"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Documento -->
                            <div class="col-12 col-md-4">
                                <q-input
                                    v-model="form.documento_numero"
                                    label="Número Documento *"
                                    :rules="[val => !!val || 'Requerido']"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Sexo -->
                            <div class="col-12 col-md-4">
                                <q-select
                                    v-model="form.sexo"
                                    :options="sexoOptions"
                                    label="Sexo"
                                    outlined
                                    dense
                                    emit-value
                                    map-options
                                />
                            </div>

                            <!-- Fecha Nacimiento -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.fecha_nacimiento"
                                    label="Fecha de Nacimiento"
                                    type="date"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Fecha Ingreso -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.fecha_ingreso"
                                    label="Fecha de Ingreso"
                                    type="date"
                                    outlined
                                    dense
                                />
                            </div>
                        </div>

                        <q-separator class="q-my-sm" />

                        <!-- SECCIÓN: Información de Contacto -->
                        <div class="text-subtitle2 text-primary q-mb-xs">Información de Contacto</div>
                        <div class="row q-col-gutter-sm">
                            <!-- Email Personal -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.email_personal"
                                    label="Email Personal"
                                    type="email"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Email Corporativo -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.email_corporativo"
                                    label="Email Corporativo"
                                    type="email"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Teléfono -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.telefono"
                                    label="Teléfono"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Dirección -->
                            <div class="col-12 col-md-6">
                                <q-input
                                    v-model="form.direccion"
                                    label="Dirección"
                                    outlined
                                    dense
                                />
                            </div>

                            <!-- Departamento -->
                            <div class="col-12 col-md-6">
                                <q-select
                                    v-model="form.departamento"
                                    :options="departamentos"
                                    option-value="name"
                                    option-label="name"
                                    emit-value
                                    map-options
                                    use-input
                                    input-debounce="0"
                                    @filter="filterDepartamentos"
                                    @update:model-value="onDepartamentoChange"
                                    label="Departamento"
                                    outlined
                                    dense
                                    :loading="loadingDepartamentos"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="map" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>

                            <!-- Ciudad -->
                            <div class="col-12 col-md-6">
                                <q-select
                                    v-model="form.ciudad"
                                    :options="ciudades"
                                    option-value="name"
                                    option-label="name"
                                    emit-value
                                    map-options
                                    use-input
                                    input-debounce="0"
                                    @filter="filterCiudades"
                                    label="Ciudad"
                                    outlined
                                    dense
                                    :loading="loadingCiudades"
                                    :disable="!form.departamento"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="location_city" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>
                        </div>

                        <q-separator class="q-my-sm" />

                        <!-- SECCIÓN: Información Laboral -->
                        <div class="text-subtitle2 text-primary q-mb-xs">Información Laboral</div>
                        <div class="row q-col-gutter-sm">
                            <!-- Cargo -->
                            <div class="col-12">
                                <q-select
                                    v-model="form.cargo_id"
                                    :options="cargos"
                                    option-value="id"
                                    option-label="nombre"
                                    emit-value
                                    map-options
                                    label="Cargo *"
                                    :rules="[val => !!val || 'Requerido']"
                                    outlined
                                    dense
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="work" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>
                        </div>

                        <q-separator class="q-my-sm" />

                        <!-- SECCIÓN: Crear Usuario (solo al crear) -->
                        <div v-if="!form.id">
                            <div class="text-subtitle2 text-primary q-mb-xs">Acceso al Sistema</div>
                            <q-checkbox
                                v-model="form.crear_usuario"
                                label="Crear usuario para acceso al sistema"
                                color="primary"
                            />
                            
                            <div v-if="form.crear_usuario" class="q-mt-sm">
                                <q-banner class="bg-blue-1 text-blue-9 q-mb-sm" dense rounded>
                                    <template v-slot:avatar>
                                        <q-icon name="info" color="blue" />
                                    </template>
                                    Se creará un usuario con el email corporativo. Contraseña inicial: <strong>password123</strong>
                                </q-banner>

                                <q-select
                                    v-model="form.rol"
                                    :options="rolesOptions"
                                    label="Rol del Sistema *"
                                    :rules="[val => !!val || 'Debe seleccionar un rol']"
                                    outlined
                                    dense
                                    emit-value
                                    map-options
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="admin_panel_settings" color="primary" size="xs" />
                                    </template>
                                </q-select>
                                <div class="text-caption text-grey-6 q-mt-xs">
                                    Define los permisos y accesos en el sistema
                                </div>
                            </div>
                        </div>
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

        <!-- DIALOG ASIGNACIONES -->
        <q-dialog v-model="dialogAsignaciones" persistent>
            <q-card style="min-width: 500px">
                <q-card-section class="row items-center q-pb-xs bg-info text-white">
                    <q-icon name="link" size="24px" class="q-mr-sm" />
                    <div class="text-h6">Asignaciones</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-sm">
                        {{ empleadoSeleccionado?.nombres }} {{ empleadoSeleccionado?.apellidos }}
                    </div>
                    <div class="text-caption text-grey-6">
                        Gestiona las asignaciones de este empleado
                    </div>
                    
                    <!-- Contenido de asignaciones - Por implementar -->
                    <div class="q-mt-md text-center text-grey-6">
                        Funcionalidad de asignaciones próximamente
                    </div>
                </q-card-section>

                <q-separator />

                <q-card-section class="row justify-end q-pt-xs q-pb-sm">
                    <q-btn
                        label="Cerrar"
                        color="grey-7"
                        flat
                        v-close-popup
                    />
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- DIALOG CREAR USUARIO -->
        <q-dialog v-model="dialogCrearUsuario" persistent>
            <q-card style="min-width: 450px">
                <q-card-section class="row items-center q-pb-xs bg-positive text-white">
                    <q-icon name="person_add" size="24px" class="q-mr-sm" />
                    <div class="text-h6">Crear Usuario</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-md">
                        Empleado: <strong>{{ selectedEmpleado?.nombres }} {{ selectedEmpleado?.apellidos }}</strong>
                    </div>

                    <q-select
                        v-model="formUsuario.rol"
                        :options="rolesOptions"
                        label="Rol *"
                        outlined
                        dense
                        emit-value
                        map-options
                        class="q-mb-sm"
                    />

                    <q-input
                        v-model="formUsuario.password"
                        label="Contraseña *"
                        :type="showPasswordUsuario ? 'text' : 'password'"
                        outlined
                        dense
                        class="q-mb-sm"
                    >
                        <template v-slot:append>
                            <q-icon
                                :name="showPasswordUsuario ? 'visibility_off' : 'visibility'"
                                class="cursor-pointer"
                                @click="showPasswordUsuario = !showPasswordUsuario"
                            />
                        </template>
                    </q-input>

                    <q-input
                        v-model="formUsuario.password_confirmation"
                        label="Confirmar Contraseña *"
                        :type="showPasswordUsuario ? 'text' : 'password'"
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
                        label="Crear Usuario"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="crearUsuario"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import departamentosData from '@/json/departments.json';
import ciudadesData from '@/json/cities.json';

const $q = useQuasar();

// Estado
const empleados = ref([]);
const cargos = ref([]);
const tiposDocumento = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialog = ref(false);
const dialogAsignaciones = ref(false);
const dialogCrearUsuario = ref(false);
const search = ref('');
const filtroCargo = ref(null);
const filtroEstado = ref('todos');
const empleadoSeleccionado = ref(null);
const selectedEmpleado = ref(null);
const showPasswordUsuario = ref(false);

// Datos de ubicación
const departamentos = ref([]);
const departamentosOriginal = ref([]);
const ciudades = ref([]);
const ciudadesOriginal = ref([]);
const loadingDepartamentos = ref(false);
const loadingCiudades = ref(false);

// Opciones de filtro
const opcionesEstado = [
    { label: 'Todos', value: 'todos' },
    { label: 'Activos', value: 'activos' },
    { label: 'Inactivos', value: 'inactivos' },
];

const sexoOptions = [
    { label: 'Masculino', value: 'M' },
    { label: 'Femenino', value: 'F' },
    { label: 'Otro', value: 'Otro' },
];

const cargosOptions = ref([
    { label: 'Inactivo', value: false },
]);

const rolesOptions = ref([]);

const formUsuario = ref({
    rol: null,
    password: '',
    password_confirmation: '',
});

// Formulario
const form = ref({
    id: null,
    nombres: '',
    apellidos: '',
    tipo_documento_id: null,
    documento_numero: '',
    fecha_nacimiento: null,
    sexo: null,
    email_personal: '',
    email_corporativo: '',
    telefono: '',
    direccion: '',
    ciudad: '',
    departamento: '',
    fecha_ingreso: null,
    cargo_id: null,
    crear_usuario: false,
    rol: null,
});

// Columnas de la tabla
const columns = [
    {
        name: 'nombres',
        label: 'Nombre',
        align: 'left',
        field: row => `${row.nombres} ${row.apellidos}`,
        sortable: true,
    },
    {
        name: 'documento_numero',
        label: 'Documento',
        align: 'left',
        field: 'documento_numero',
    },
    {
        name: 'cargo',
        label: 'Cargo',
        align: 'left',
    },
    {
        name: 'email_corporativo',
        label: 'Email',
        align: 'left',
        field: 'email_corporativo',
    },
    {
        name: 'telefono',
        label: 'Teléfono',
        align: 'left',
        field: 'telefono',
    },
    {
        name: 'usuario',
        label: 'Usuario',
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
const loadEmpleados = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/empleados', {
            params: {
                search: search.value,
                cargo_id: filtroCargo.value,
                estado: filtroEstado.value,
            },
        });
        empleados.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar los empleados',
        });
    } finally {
        loading.value = false;
    }
};

const loadCargos = async () => {
    try {
        const response = await axios.get('/api/empleados-cargos');
        cargos.value = response.data;
        
        // Actualizar opciones de filtro
        cargosOptions.value = [
            { label: 'Todos los cargos', value: null },
            ...response.data.map(c => ({ label: c.nombre, value: c.id }))
        ];
    } catch (error) {
        console.error('Error al cargar cargos:', error);
    }
};

const loadTiposDocumento = async () => {
    try {
        const response = await axios.get('/api/tipos-documento');
        tiposDocumento.value = response.data;
    } catch (error) {
        console.error('Error al cargar tipos de documento:', error);
    }
};

const loadRoles = async () => {
    try {
        const response = await axios.get('/api/roles');
        rolesOptions.value = response.data
            .filter(role => role.name !== 'super-admin') // Excluir super-admin
            .map(role => ({
                label: role.name.charAt(0).toUpperCase() + role.name.slice(1).replace(/_/g, ' '),
                value: role.name,
            }));
    } catch (error) {
        console.error('Error al cargar roles:', error);
        rolesOptions.value = [
            { label: 'Admin', value: 'admin' },
            { label: 'Agente', value: 'agente' },
            { label: 'Visitador', value: 'visitador' },
            { label: 'Profesional', value: 'profesional' },
        ];
    }
};

// Función auxiliar para formatear fechas de Laravel a formato YYYY-MM-DD
const formatDate = (date) => {
    if (!date) return null;
    // Si ya está en formato YYYY-MM-DD, devolverlo tal cual
    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date;
    }
    // Si es un objeto Date o string ISO, extraer solo la fecha
    const d = new Date(date);
    if (isNaN(d.getTime())) return null;
    return d.toISOString().split('T')[0];
};

const openDialog = (empleado = null) => {
    console.log(empleado)
    if (empleado) {
        form.value = {
            id: empleado.id,
            nombres: empleado.nombres,
            apellidos: empleado.apellidos,
            tipo_documento_id: empleado.tipo_documento_id,
            documento_numero: empleado.documento_numero,
            fecha_nacimiento: formatDate(empleado.fecha_nacimiento),
            sexo: empleado.sexo,
            email_personal: empleado.email_personal,
            email_corporativo: empleado.email_corporativo,
            telefono: empleado.telefono,
            direccion: empleado.direccion,
            ciudad: empleado.ciudad,
            departamento: empleado.departamento,
            fecha_ingreso: formatDate(empleado.fecha_ingreso),
            cargo_id: empleado.cargo_principal?.[0]?.id || null,
            crear_usuario: false,
            rol: null,
        };
    } else {
        form.value = {
            id: null,
            nombres: '',
            apellidos: '',
            tipo_documento_id: null,
            documento_numero: '',
            fecha_nacimiento: null,
            sexo: null,
            email_personal: '',
            email_corporativo: '',
            telefono: '',
            direccion: '',
            ciudad: '',
            departamento: '',
            fecha_ingreso: null,
            cargo_id: null,
            crear_usuario: false,
            rol: null,
        };
    }
    dialog.value = true;
};

const save = async () => {
    saving.value = true;
    try {
        // Preparar datos para enviar (convertir fechas vacías a null)
        const dataToSend = {
            ...form.value,
            fecha_nacimiento: form.value.fecha_nacimiento || null,
            fecha_ingreso: form.value.fecha_ingreso || null,
            // Convertir cargo_id a array de cargos
            cargos: form.value.cargo_id ? [form.value.cargo_id] : [],
        };

        // Eliminar cargo_id del objeto (ya lo enviamos como cargos)
        delete dataToSend.cargo_id;

        if (form.value.id) {
            // Actualizar
            await axios.put(`/api/empleados/${form.value.id}`, dataToSend);
            $q.notify({
                type: 'positive',
                message: 'Empleado actualizado exitosamente',
            });
        } else {
            // Crear
            await axios.post('/api/empleados', dataToSend);
            $q.notify({
                type: 'positive',
                message: 'Empleado creado exitosamente',
            });
        }
        dialog.value = false;
        loadEmpleados();
    } catch (error) {
        const message = error.response?.data?.message || 'Error al guardar el empleado';
        $q.notify({
            type: 'negative',
            message: message,
        });
    } finally {
        saving.value = false;
    }
};

const toggleStatus = async (empleado) => {
    const action = empleado.is_active ? 'desactivar' : 'activar';
    
    $q.dialog({
        title: 'Confirmar',
        message: `¿Está seguro de ${action} este empleado?`,
        cancel: true,
        persistent: true,
    }).onOk(async () => {
        try {
            if (empleado.is_active) {
                await axios.delete(`/api/empleados/${empleado.id}`);
            } else {
                await axios.patch(`/api/empleados/${empleado.id}/restore`);
            }
            $q.notify({
                type: 'positive',
                message: `Empleado ${action === 'desactivar' ? 'desactivado' : 'activado'} exitosamente`,
            });
            loadEmpleados();
        } catch (error) {
            $q.notify({
                type: 'negative',
                message: `Error al ${action} el empleado`,
            });
        }
    });
};

const openAsignacionesDialog = (empleado) => {
    empleadoSeleccionado.value = empleado;
    dialogAsignaciones.value = true;
};

// Métodos de ubicación (igual que PacientesComponent)
const loadDepartamentos = () => {
    loadingDepartamentos.value = true;
    try {
        departamentosOriginal.value = departamentosData.data;
        departamentos.value = departamentosData.data;
    } catch (error) {
        console.error('Error al cargar departamentos:', error);
        $q.notify({
            type: 'negative',
            message: 'Error al cargar departamentos',
        });
    } finally {
        loadingDepartamentos.value = false;
    }
};

const filterDepartamentos = (val, update) => {
    update(() => {
        if (val === '') {
            departamentos.value = departamentosOriginal.value;
        } else {
            const needle = val.toLowerCase();
            departamentos.value = departamentosOriginal.value.filter(
                v => v.name.toLowerCase().indexOf(needle) > -1
            );
        }
    });
};

const onDepartamentoChange = (val) => {
    form.value.ciudad = '';
    if (!val) {
        ciudades.value = [];
        return;
    }

    loadingCiudades.value = true;
    try {
        const dept = departamentosOriginal.value.find(d => d.name === val);
        if (dept) {
            // Filtrar ciudades por departmentId desde el JSON local
            const ciudadesDept = ciudadesData.data.filter(c => c.departmentId === dept.id);
            ciudadesOriginal.value = ciudadesDept;
            ciudades.value = ciudadesDept;
        }
    } catch (error) {
        console.error('Error al cargar ciudades:', error);
        $q.notify({
            type: 'negative',
            message: 'Error al cargar ciudades',
        });
    } finally {
        loadingCiudades.value = false;
    }
};

const filterCiudades = (val, update) => {
    update(() => {
        if (val === '') {
            ciudades.value = ciudadesOriginal.value;
        } else {
            const needle = val.toLowerCase();
            ciudades.value = ciudadesOriginal.value.filter(
                v => v.name.toLowerCase().indexOf(needle) > -1
            );
        }
    });
};

const openCrearUsuarioDialog = (empleado) => {
    selectedEmpleado.value = empleado;
    formUsuario.value = {
        rol: null,
        password: '',
        password_confirmation: '',
    };
    showPasswordUsuario.value = false;
    dialogCrearUsuario.value = true;
};

const crearUsuario = async () => {
    // Validaciones
    if (!formUsuario.value.rol) {
        $q.notify({
            type: 'negative',
            message: 'Debe seleccionar un rol',
        });
        return;
    }

    if (!formUsuario.value.password || formUsuario.value.password.length < 8) {
        $q.notify({
            type: 'negative',
            message: 'La contraseña debe tener al menos 8 caracteres',
        });
        return;
    }

    if (formUsuario.value.password !== formUsuario.value.password_confirmation) {
        $q.notify({
            type: 'negative',
            message: 'Las contraseñas no coinciden',
        });
        return;
    }

    saving.value = true;
    try {
        await axios.post(`/api/empleados/${selectedEmpleado.value.id}/crear-usuario`, {
            password: formUsuario.value.password,
            rol: formUsuario.value.rol,
        });

        $q.notify({
            type: 'positive',
            message: 'Usuario creado y vinculado exitosamente',
        });

        dialogCrearUsuario.value = false;
        loadEmpleados();
    } catch (error) {
        const message = error.response?.data?.message || 'Error al crear el usuario';
        $q.notify({
            type: 'negative',
            message: message,
        });
    } finally {
        saving.value = false;
    }
};

// Lifecycle
onMounted(() => {
    loadDepartamentos();
    loadTiposDocumento();
    loadCargos();
    loadRoles();
    loadEmpleados();
});
</script>

<style scoped>
.empleados-container {
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
