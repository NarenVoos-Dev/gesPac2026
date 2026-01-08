<template>
    <div class="pacientes-container">
        <!-- HEADER CON BÚSQUEDA Y BOTÓN NUEVO -->
        <div class="row items-center justify-between q-mb-md q-gutter-sm">
            <div class="col-12 col-md-auto">
                <div class="text-h6 text-grey-9">Pacientes</div>
                <div class="text-caption text-grey-6">Gestiona la información de los pacientes</div>
            </div>
            
            <div class="col-12 col-md row items-center justify-end q-gutter-sm">
                <!-- Búsqueda -->
                <q-input
                    v-model="search"
                    outlined
                    dense
                    placeholder="Buscar por nombre o documento..."
                    style="min-width: 250px"
                    @update:model-value="loadPacientes"
                >
                    <template v-slot:prepend>
                        <q-icon name="search" />
                    </template>
                    <template v-slot:append v-if="search">
                        <q-icon name="close" class="cursor-pointer" @click="search = ''; loadPacientes()" />
                    </template>
                </q-input>

                <!-- Filtro de estado -->
                <q-select
                    
                    v-model="filtroEstado"
                    :options="opcionesEstado"
                    outlined
                    dense
                    style="min-width: 150px"
                    @update:model-value="loadPacientes"
                />

                <!-- Botón Nuevo -->
                <q-btn
                    size="md"
                    color="primary"
                    icon="add"
                    label="Nuevo Paciente"
                    @click="openDialog()"
                    unelevated
                />
            </div>
        </div>

        <!-- TABLA DE PACIENTES -->
        <q-table
            :rows="pacientes"
            :columns="columns"
            row-key="id"
            :loading="loading"
            flat
            bordered
            dense
            :rows-per-page-options="[10, 25, 50, 100]"
            class="modern-table"
        >
            <!-- COLUMNA: Nombre Completo -->
            <template v-slot:body-cell-nombre_completo="props">
                <q-td :props="props">
                    <div class="text-weight-medium">{{ props.row.nombre_completo }} ({{ props.row.edad }} años)</div>
                    <div class="text-caption text-grey-6"></div>
                </q-td>
            </template>

            <!-- COLUMNA: Documento -->
            <template v-slot:body-cell-documento="props">
                <q-td :props="props">
                    <div>{{ props.row.tipo_documento?.codigo }} - {{ props.row.documento_numero }}</div>
                </q-td>
            </template>

            <!-- COLUMNA: Ubicación -->
            <template v-slot:body-cell-ubicacion="props">
                <q-td :props="props">
                    <div v-if="props.row.departamento">{{ props.row.departamento }} - {{ props.row.municipio }}</div>
                </q-td>
            </template>

            <!-- COLUMNA: Sexo -->
            <template v-slot:body-cell-sexo="props">
                <q-td :props="props">
                    <div class="row items-center justify-center q-gutter-xs">
                        <q-icon 
                            :name="props.row.sexo === 'F' ? 'woman' : props.row.sexo === 'M' ? 'man' : 'person'"
                            :color="props.row.sexo === 'F' ? 'pink' : props.row.sexo === 'M' ? 'blue' : 'grey'"
                            size="20px"
                        />
                        <span>{{ props.row.sexo }}</span>
                    </div>
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
                        icon="visibility"
                        color="info"
                        @click="openDialog(props.row, true)"
                    >
                        <q-tooltip>Ver</q-tooltip>
                    </q-btn>
                    <q-btn
                        size="sm"
                        flat
                        dense
                        round
                        icon="edit"
                        color="primary"
                        @click="openDialog(props.row, false)"
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
        <q-dialog v-model="dialog" persistent transition-show="scale" transition-hide="scale" maximized>
            <q-card class="modern-dialog">
                <!-- HEADER -->
                <q-card-section class="row items-center q-pb-none bg-primary text-white">
                    <q-icon :name="readOnly ? 'visibility' : 'person_add'" size="24px" class="q-mr-sm" />
                    <div class="text-h6">{{ readOnly ? 'Ver' : (form.id ? 'Editar' : 'Nuevo') }} Paciente</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>

                <q-separator />

                <!-- FORMULARIO -->
                <q-card-section class="q-pt-xs" style="max-height: calc(100vh - 120px); overflow-y: auto;">
                    <q-form @submit="save" class="q-gutter-xs">
                        <!-- SECCIÓN: INFORMACIÓN PERSONAL -->
                        <div class="text-subtitle2 text-grey-8 q-mt-xs q-mb-xs">
                            <q-icon name="person" class="q-mr-xs" />
                            Información Personal
                        </div>
                        <q-separator class="q-mb-md" />

                        <div class="row q-col-gutter-md">
                            <!-- Primer Nombre -->
                            <div class="col-12 col-md-3">
                                <q-input
                                    v-model="form.p_nombres"
                                    label="Primer Nombre *"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                    :rules="[val => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="person" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Segundo Nombre -->
                            <div class="col-12 col-md-3">
                                <q-input
                                    v-model="form.s_nombres"
                                    label="Segundo Nombre"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="person_outline" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Primer Apellido -->
                            <div class="col-12 col-md-3">
                                <q-input
                                    v-model="form.p_apellidos"
                                    label="Primer Apellido *"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                    :rules="[val => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="person" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Segundo Apellido -->
                            <div class="col-12 col-md-3">
                                <q-input
                                    v-model="form.s_apellidos"
                                    label="Segundo Apellido"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="person_outline" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>
                        </div>

                        <!-- SECCIÓN: DOCUMENTO -->
                        <div class="text-subtitle2 text-grey-8 q-mt-xs q-mb-xs">
                            <q-icon name="badge" class="q-mr-xs" />
                            Documento de Identidad
                        </div>
                        <q-separator class="q-mb-md" />

                        <div class="row q-col-gutter-md">
                            <!-- Tipo de Documento -->
                            <div class="col-12 col-md-4">
                                <q-select
                                    v-model="form.tipo_documento_id"
                                    :options="tiposDocumento"
                                    option-value="id"
                                    option-label="nombre"
                                    emit-value
                                    map-options
                                    label="Tipo de Documento *"
                                    outlined
                                    dense
                                    :disable="readOnly"
                                    :rules="[val => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="badge" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>

                            <!-- Número de Documento -->
                            <div class="col-12 col-md-4">
                                <q-input
                                    v-model="form.documento_numero"
                                    label="Número de Documento *"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                    :rules="[val => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="numbers" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Fecha de Nacimiento -->
                            <div class="col-12 col-md-4">
                                <q-input
                                    v-model="form.fecha_nacimiento"
                                    label="Fecha de Nacimiento *"
                                    type="date"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                    :rules="[val => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="cake" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>
                        </div>

                        <div class="row q-col-gutter-md">
                            <!-- Sexo -->
                            <div class="col-12 col-md-4">
                                <q-select
                                    v-model="form.sexo"
                                    :options="opcionesSexo"
                                    option-value="value"
                                    option-label="label"
                                    emit-value
                                    map-options
                                    label="Sexo *"
                                    outlined
                                    dense
                                    :disable="readOnly"
                                    :rules="[val => !!val || 'Requerido']"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="wc" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>
                        </div>

                        <!-- SECCIÓN: CONTACTO -->
                        <div class="text-subtitle2 text-grey-8 q-mt-xs q-mb-xs">
                            <q-icon name="contact_phone" class="q-mr-xs" />
                            Información de Contacto
                        </div>
                        <q-separator class="q-mb-md" />

                        <div class="row q-col-gutter-md">
                            <!-- Teléfono -->
                            <div class="col-12 col-md-4">
                                <q-input
                                    v-model="form.telefono"
                                    label="Teléfono"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="phone" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-4">
                                <q-input
                                    v-model="form.email"
                                    label="Correo Electrónico"
                                    type="email"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="email" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Dirección -->
                            <div class="col-12 col-md-4">
                                <q-input
                                    v-model="form.direccion"
                                    label="Dirección"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="home" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>
                        </div>

                        <!-- SECCIÓN: UBICACIÓN -->
                        <div class="text-subtitle2 text-grey-8 q-mt-md q-mb-sm">
                            <q-icon name="location_on" class="q-mr-xs" />
                            Ubicación
                        </div>
                        <q-separator class="q-mb-md" />

                        <div class="row q-col-gutter-md">
                            <!-- Departamento -->
                            <div class="col-12 col-md-3">
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
                                    :disable="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="map" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>

                            <!-- Municipio -->
                            <div class="col-12 col-md-3">
                                <q-select
                                    v-model="form.municipio"
                                    :options="municipios"
                                    option-value="name"
                                    option-label="name"
                                    emit-value
                                    map-options
                                    use-input
                                    input-debounce="0"
                                    @filter="filterMunicipios"
                                    label="Municipio"
                                    outlined
                                    dense
                                    :loading="loadingMunicipios"
                                    :disable="readOnly || !form.departamento"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="location_city" color="primary" size="xs" />
                                    </template>
                                </q-select>
                            </div>

                            <!-- Ciudad -->
                            <div class="col-12 col-md-3">
                                <q-input
                                    v-model="form.ciudad"
                                    label="Barrio"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="apartment" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>

                            <!-- Código Postal -->
                            <div class="col-12 col-md-3">
                                <q-input
                                    v-model="form.codigo_postal"
                                    label="Código Postal"
                                    outlined
                                    dense
                                    :readonly="readOnly"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="markunread_mailbox" color="primary" size="xs" />
                                    </template>
                                </q-input>
                            </div>
                        </div>

                        <!-- SECCIÓN: OBSERVACIONES -->
                        <div class="text-subtitle2 text-grey-8 q-mt-md q-mb-sm">
                            <q-icon name="notes" class="q-mr-xs" />
                            Observaciones
                        </div>
                        <q-separator class="q-mb-md" />

                        <q-input
                            v-model="form.observaciones"
                            label="Observaciones"
                            type="textarea"
                            outlined
                            rows="3"
                            :readonly="readOnly"
                        />

                        <!-- Estado -->
                        <div class="q-mt-md">
                            <q-toggle
                                v-model="form.is_active"
                                label="Activo"
                                color="positive"
                                size="md"
                                :disable="readOnly"
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
                        v-if="!readOnly"
                        label="Cancelar"
                        color="grey-7"
                        flat
                        v-close-popup
                        icon="close"
                    />
                    <q-btn
                        v-if="!readOnly"
                        label="Guardar"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="save"
                        icon="save"
                    />
                    <q-btn
                        v-if="readOnly"
                        label="Cerrar"
                        color="primary"
                        flat
                        v-close-popup
                        icon="close"
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

// Importar datos locales de Colombia
import departamentosData from '@/json/departments.json';
import ciudadesData from '@/json/cities.json';

const $q = useQuasar();

// Estado
const pacientes = ref([]);
const tiposDocumento = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialog = ref(false);
const readOnly = ref(false);
const search = ref('');
const filtroEstado = ref({ label: 'Todos', value: 'todos' });

// API Colombia - Datos locales
const departamentos = ref([]);
const departamentosOriginal = ref([]);
const municipios = ref([]);
const municipiosOriginal = ref([]);
const loadingDepartamentos = ref(false);
const loadingMunicipios = ref(false);

const opcionesEstado = [
    { label: 'Todos', value: 'todos' },
    { label: 'Activos', value: 'activos' },
    { label: 'Inactivos', value: 'inactivos' },
];

const opcionesSexo = [
    { label: 'Masculino', value: 'M' },
    { label: 'Femenino', value: 'F' },
    { label: 'Otro', value: 'Otro' },
];

const form = ref({
    id: null,
    p_nombres: '',
    s_nombres: '',
    p_apellidos: '',
    s_apellidos: '',
    tipo_documento_id: null,
    documento_numero: '',
    fecha_nacimiento: '',
    sexo: '',
    telefono: '',
    email: '',
    direccion: '',
    departamento: '',
    municipio: '',
    ciudad: '',
    codigo_postal: '',
    observaciones: '',
    is_active: true,
});

// Columnas de la tabla
const columns = [
    {
        name: 'nombre_completo',
        label: 'Nombre Completo',
        align: 'left',
        field: 'nombre_completo',
        sortable: true,
        style: 'width: 25%',
        headerStyle: 'width: 25%',
    },
    {
        name: 'documento',
        label: 'Documento',
        align: 'left',
        sortable: true,
        style: 'width: 15%',
        headerStyle: 'width: 15%',
    },
    {
        name: 'sexo',
        label: 'Sexo',
        align: 'center',
        field: 'sexo',
        sortable: true,
        style: 'width: 8%',
        headerStyle: 'width: 8%',
    },
    {
        name: 'email',
        label: 'Correo',
        align: 'left',
        field: 'email',
        style: 'width: 12%',
        headerStyle: 'width: 12%',
    },
    {
        name: 'telefono',
        label: 'Teléfono',
        align: 'left',
        field: 'telefono',
        style: 'width: 8%',
        headerStyle: 'width: 8%',
    },
    {
        name: 'direccion',
        label: 'Dirección',
        align: 'left',
        field: 'direccion',
        style: 'width: 12%',
        headerStyle: 'width: 12%',
    },
    {
        name: 'ubicacion',
        label: 'Ubicación',
        align: 'left',
        style: 'width: 12%',
        headerStyle: 'width: 12%',
    },
    {
        name: 'is_active',
        label: 'Estado',
        align: 'center',
        field: 'is_active',
        sortable: true,
        style: 'width: 8%',
        headerStyle: 'width: 8%',
    },
    {
        name: 'acciones',
        label: 'Acciones',
        align: 'center',
        style: 'width: 12%',
        headerStyle: 'width: 12%',
    },
];

// Cargar departamentos desde JSON local
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
            position: 'top',
        });
    } finally {
        loadingDepartamentos.value = false;
    }
};

// Filtrar departamentos
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

// Cargar municipios cuando cambia el departamento
const onDepartamentoChange = (val) => {
    form.value.municipio = '';
    if (!val) {
        municipios.value = [];
        return;
    }

    loadingMunicipios.value = true;
    try {
        const dept = departamentosOriginal.value.find(d => d.name === val);
        if (dept) {
            // Filtrar ciudades por departmentId desde el JSON local
            const ciudadesDept = ciudadesData.data.filter(c => c.departmentId === dept.id);
            municipiosOriginal.value = ciudadesDept;
            municipios.value = ciudadesDept;
        }
    } catch (error) {
        console.error('Error al cargar municipios:', error);
        $q.notify({
            type: 'negative',
            message: 'Error al cargar municipios',
            position: 'top',
        });
    } finally {
        loadingMunicipios.value = false;
    }
};

// Filtrar municipios
const filterMunicipios = (val, update) => {
    update(() => {
        if (val === '') {
            municipios.value = municipiosOriginal.value;
        } else {
            const needle = val.toLowerCase();
            municipios.value = municipiosOriginal.value.filter(
                v => v.name.toLowerCase().indexOf(needle) > -1
            );
        }
    });
};

// Cargar pacientes
const loadPacientes = async () => {
    loading.value = true;
    try {
        const params = {
            search: search.value,
            estado: filtroEstado.value.value,
        };
        const response = await axios.get('/api/pacientes', { params });
        pacientes.value = response.data;
    } catch (error) {
        $q.notify({
            type: 'negative',
            message: 'Error al cargar pacientes',
            position: 'top',
        });
    } finally {
        loading.value = false;
    }
};

// Cargar tipos de documento
const loadTiposDocumento = async () => {
    try {
        const response = await axios.get('/api/pacientes/tipos-documento');
        tiposDocumento.value = response.data;
    } catch (error) {
        console.error('Error al cargar tipos de documento:', error);
    }
};

// Abrir dialog
const openDialog = async (paciente = null, readOnlyMode = false) => {
    readOnly.value = readOnlyMode;
    
    if (paciente) {
        form.value = { ...paciente };
        
        // Formatear fecha de nacimiento para el input type="date" (solo YYYY-MM-DD)
        if (paciente.fecha_nacimiento) {
            form.value.fecha_nacimiento = paciente.fecha_nacimiento.split(' ')[0];
        }
        
        // Cargar municipios si hay departamento (y esperar a que cargue)
        if (paciente.departamento) {
            await onDepartamentoChange(paciente.departamento);
            // Restaurar el municipio después de cargar la lista
            form.value.municipio = paciente.municipio;
        }
    } else {
        form.value = {
            id: null,
            p_nombres: '',
            s_nombres: '',
            p_apellidos: '',
            s_apellidos: '',
            tipo_documento_id: null,
            documento_numero: '',
            fecha_nacimiento: '',
            sexo: '',
            telefono: '',
            email: '',
            direccion: '',
            departamento: '',
            municipio: '',
            ciudad: '',
            codigo_postal: '',
            observaciones: '',
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
            await axios.put(`/api/pacientes/${form.value.id}`, form.value);
            $q.notify({
                type: 'positive',
                message: 'Paciente actualizado correctamente',
                position: 'top',
            });
        } else {
            // Crear
            await axios.post('/api/pacientes', form.value);
            $q.notify({
                type: 'positive',
                message: 'Paciente creado correctamente',
                position: 'top',
            });
        }
        dialog.value = false;
        loadPacientes();
    } catch (error) {
        console.error('Error al guardar:', error);
        
        // Mostrar errores de validación específicos
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat();
            
            // Crear mensaje HTML con lista
            const htmlMessage = errorMessages.map(msg => `• ${msg}`).join('<br>');
            
            $q.notify({
                type: 'negative',
                message: 'Por favor corrija los siguientes errores:',
                caption: htmlMessage,
                html: true,
                position: 'top',
                timeout: 6000,
                multiLine: true,
                classes: 'error-notification',
                textColor: 'white',
            });
        } else {
            $q.notify({
                type: 'negative',
                message: error.response?.data?.message || 'Error al guardar el paciente',
                position: 'top',
                textColor: 'white',
            });
        }
    } finally {
        saving.value = false;
    }
};

// Cambiar estado con confirmación
const toggleStatus = async (paciente) => {
    if (paciente.is_active) {
        $q.dialog({
            title: 'Confirmar desactivación',
            message: `¿Está seguro que desea desactivar al paciente "${paciente.nombre_completo}"? No podrá crear nuevas prescripciones hasta que lo reactive.`,
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
                await axios.patch(`/api/pacientes/${paciente.id}/toggle-status`);
                $q.notify({
                    type: 'positive',
                    message: 'Paciente desactivado correctamente',
                    position: 'top',
                });
                loadPacientes();
            } catch (error) {
                $q.notify({
                    type: 'negative',
                    message: 'Error al cambiar estado',
                    position: 'top',
                });
            }
        });
    } else {
        try {
            await axios.patch(`/api/pacientes/${paciente.id}/toggle-status`);
            $q.notify({
                type: 'positive',
                message: 'Paciente activado correctamente',
                position: 'top',
            });
            loadPacientes();
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
    loadPacientes();
    loadTiposDocumento();
    loadDepartamentos();
});
</script>

<style scoped>
.pacientes-container {
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
    padding: 4px 8px;
}

:deep(.q-table tbody td) {
    font-size: 12px;
    padding: 2px 8px;
}

:deep(.q-table tbody tr) {
    height: 32px;
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

/* Notificación de errores más visible */
:deep(.error-notification) {
    background-color: #C62828 !important;
    color: white !important;
    font-size: 14px !important;
    min-width: 400px !important;
}

:deep(.error-notification .q-notification__message) {
    color: white !important;
    font-weight: 600 !important;
}

:deep(.error-notification .q-notification__caption) {
    color: white !important;
    line-height: 1.6 !important;
    margin-top: 8px !important;
}
</style>
