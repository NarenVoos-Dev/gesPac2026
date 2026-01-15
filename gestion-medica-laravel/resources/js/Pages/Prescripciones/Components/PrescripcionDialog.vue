<template>
    <div class="q-pa-md q-gutter-sm">
        <q-dialog v-model="dialog" persistent maximized transition-show="slide-up" transition-hide="slide-down">
            <q-card class="bg-grey-1">
                <!-- HEADER -->
                <q-bar class="bg-primary text-white">
                    <q-icon name="local_hospital" />
                    <div>
                        <span v-if="readonly">Detalle de Prescripción</span>
                        <span v-else>{{ form.id ? 'Editar Prescripción' : 'Nueva Prescripción Médica' }}</span>
                    </div>
                    <q-space />
                    <q-btn dense flat icon="close" v-close-popup>
                        <q-tooltip>Cerrar</q-tooltip>
                    </q-btn>
                </q-bar>
                
                <q-card-section class="q-pa-md scroll" style="height: calc(100vh - 50px)">
                    <q-form @submit="save" class="q-gutter-md">
                        
                        <!-- SECCIÓN 1: PACIENTE -->
                        <div class="q-mb-lg">
                            <div class="row items-center justify-between q-mb-sm">
                                <div class="text-subtitle1 text-weight-bold text-primary">
                                    <q-icon name="person" class="q-mr-xs" />
                                    Datos del Paciente
                                </div>
                                    <div class="row q-col-gutter-sm" v-if="!selectedPaciente.id && !readonly">
                                    <div class="col-auto">
                                        <q-btn 
                                            color="primary" 
                                            icon="search" 
                                            label="Buscar Paciente" 
                                            @click="showPacienteSelector = true"
                                            unelevated
                                        />
                                    </div>
                                    <div class="col-auto">
                                        <q-btn
                                            color="secondary"
                                            icon="person_add"
                                            label="Nuevo"
                                            flat
                                            class="q-ml-sm"
                                            @click="showNewPacienteDialog = true"
                                        >
                                            <q-tooltip>Registro rápido de nuevo paciente</q-tooltip>
                                        </q-btn>
                                    </div>
                                </div>
                                <div v-else-if="!readonly">
                                    <q-btn 
                                        flat 
                                        color="negative" 
                                        icon="person_off" 
                                        label="Cambiar Paciente" 
                                        @click="clearPaciente"
                                        class="text-weight-bold"
                                    />
                                </div>
                            </div>
                            
                            <q-card flat bordered class="q-pa-md bg-white">
                                <div class="row q-col-gutter-md" v-if="selectedPaciente && selectedPaciente.id">
                                    <div class="col-12 col-md-2">
                                        <q-input :model-value="selectedPaciente.documento_numero" label="Documento" dense outlined readonly bg-color="grey-1" />
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <q-input :model-value="selectedPaciente.nombre_completo || `${selectedPaciente.p_nombres} ${selectedPaciente.p_apellidos}`" label="Nombre Completo" dense outlined readonly bg-color="grey-1" />
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <q-input :model-value="selectedPaciente.fecha_nacimiento" label="Fecha Nacimiento" dense outlined readonly bg-color="grey-1" />
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <q-input :model-value="selectedPaciente.sexo" label="Sexo" dense outlined readonly bg-color="grey-1" />
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <q-input :model-value="selectedPaciente.telefono" label="Teléfono" dense outlined readonly bg-color="grey-1" />
                                    </div>
                                </div>
                                <div v-else class="text-center q-pa-lg text-grey bg-grey-1 rounded-borders">
                                    <q-icon name="person_search" size="48px" color="grey-5" class="q-mb-sm" />
                                    <div class="text-h6 text-grey-6">Seleccione o cree un paciente para comenzar</div>
                                </div>
                            </q-card>
                        </div>

                        <!-- SECCIÓN 2: DATOS FORMULACIÓN -->
                        <div class="q-mb-lg">
                            <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">
                                <q-icon name="medication" class="q-mr-xs" />
                                Datos de la Formulación
                            </div>
                            <q-card flat bordered class="q-pa-md bg-white">
                                <div class="row q-col-gutter-md">
                                    <!-- NUMERO MANUAL -->
                                    <div class="col-12">
                                        <q-input 
                                            v-model="form.numero" 
                                            label="Número de Prescripción *" 
                                            outlined 
                                            dense 
                                            :readonly="readonly"
                                            :rules="[val => !!val || 'Requerido']" 
                                        >
                                            <template v-slot:prepend>
                                                <q-icon name="confirmation_number" color="primary" />
                                            </template>
                                        </q-input>
                                    </div>
                                    <!-- PROFESIONAL -->
                                    <div class="col-12 col-md-6">
                                        <q-select
                                            v-model="form.empleado_id"
                                            :options="empleados"
                                            option-value="id"
                                            :option-label="opt => `${opt.nombres} ${opt.apellidos}`"
                                            emit-value
                                            map-options
                                            label="Profesional *"
                                            outlined
                                            dense
                                            :readonly="readonly"
                                            use-input
                                            @filter="filterEmpleados"
                                            :rules="[val => !!val || 'Requerido']"
                                        >
                                            <template v-slot:prepend>
                                                <q-icon name="doctor" class="material-icons" color="primary" />
                                            </template>
                                            <template v-slot:option="scope">
                                                <q-item v-bind="scope.itemProps">
                                                    <q-item-section>
                                                        <q-item-label>{{ scope.opt.nombres }} {{ scope.opt.apellidos }}</q-item-label>
                                                    <q-item-label caption>
                                                        {{ 
                                                            scope.opt.cargo?.nombre || 
                                                            (scope.opt.cargos && scope.opt.cargos.length > 0 ? scope.opt.cargos[0].nombre : 'Sin cargo') 
                                                        }}
                                                    </q-item-label>
                                                    </q-item-section>
                                                </q-item>
                                            </template>
                                        </q-select>
                                    </div>
    
                                    <!-- PRODUCTO -->
                                    <div class="col-12 col-md-6">
                                        <q-select
                                            v-model="form.producto_id"
                                            :options="productos"
                                            option-value="id"
                                            option-label="nombre"
                                            emit-value
                                            map-options
                                            label="Producto *"
                                            outlined
                                            dense
                                            :readonly="readonly"
                                            use-input
                                            @filter="filterProductos"
                                            :rules="[val => !!val || 'Requerido']"
                                        >
                                            <template v-slot:prepend>
                                                <q-icon name="inventory_2" color="primary" />
                                            </template>
                                            <template v-slot:option="scope">
                                                <q-item v-bind="scope.itemProps">
                                                    <q-item-section>
                                                        <q-item-label>{{ scope.opt.nombre }}</q-item-label>
                                                        <q-item-label caption>Código: {{ scope.opt.codigo }}</q-item-label>
                                                    </q-item-section>
                                                </q-item>
                                            </template>
                                        </q-select>
                                    </div>
                                    
                                    <!-- VISITADOR ASIGNADO (Solo Lectura) -->
                                    <div class="col-12 col-md-12">
                                        <q-input 
                                            v-model="visitadorAsignado" 
                                            label="Visitador Médico Asignado" 
                                            outlined 
                                            dense 
                                            readonly 
                                            bg-color="grey-2" 
                                            color="secondary"
                                        >
                                            <template v-slot:prepend>
                                                <q-icon name="assignment_ind" color="secondary" />
                                            </template>
                                        </q-input>
                                    </div>

                                    <!-- CANTIDAD Y ENTREGAS -->
                                    <div class="col-12 col-md-3">
                                        <q-input
                                            v-model.number="form.cantidad_total"
                                            type="number"
                                            label="Cantidad Total *"
                                            outlined
                                            dense
                                            :rules="[val => val > 0 || 'Mínimo 1']"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <q-input
                                            v-model.number="form.numero_entregas"
                                            type="number"
                                            label="Nº Entregas *"
                                            outlined
                                            dense
                                            :rules="[val => val > 0 || 'Mínimo 1']"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <q-input
                                            v-model="form.fecha_prescripcion"
                                            label="Fecha Prescripción *"
                                            outlined
                                            dense
                                            readonly
                                            :rules="[val => !!val || 'Requerida']"
                                        >
                                            <template v-slot:append>
                                                <q-icon name="event" class="cursor-pointer">
                                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                                        <q-date v-model="form.fecha_prescripcion" mask="YYYY-MM-DD">
                                                            <div class="row items-center justify-end">
                                                                <q-btn v-close-popup label="Cerrar" color="primary" flat />
                                                            </div>
                                                        </q-date>
                                                    </q-popup-proxy>
                                                </q-icon>
                                            </template>
                                        </q-input>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <q-input
                                            v-model="form.fecha_vencimiento"
                                            label="Fecha Vencimiento *"
                                            outlined
                                            dense
                                            readonly
                                            :rules="[
                                                val => !!val || 'Requerida',
                                                val => val >= form.fecha_prescripcion || 'Debe ser posterior a prescripción'
                                            ]"
                                        >
                                            <template v-slot:append>
                                                <q-icon name="event" class="cursor-pointer">
                                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                                        <q-date v-model="form.fecha_vencimiento" mask="YYYY-MM-DD">
                                                            <div class="row items-center justify-end">
                                                                <q-btn v-close-popup label="Cerrar" color="primary" flat />
                                                            </div>
                                                        </q-date>
                                                    </q-popup-proxy>
                                                </q-icon>
                                            </template>
                                        </q-input>
                                    </div>
                                </div>
                            </q-card>
                        </div>

                        <!-- SECCIÓN 3: UBICACIÓN Y LOGÍSTICA -->
                        <div class="q-mb-lg">
                            <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">
                                <q-icon name="local_shipping" class="q-mr-xs" />
                                Datos de Entrega
                            </div>
                            <q-card flat bordered class="q-pa-md bg-white">
                                <div class="row q-col-gutter-md">
                                    <div class="col-12 col-md-4">
                                        <q-input v-model="form.ciudad" label="Ciudad" outlined dense />
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <q-input v-model="form.municipio" label="Barrio/Municipio" outlined dense />
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <q-input v-model="form.barrio" label="Sector/Barrio" outlined dense />
                                    </div>
                                    <div class="col-12">
                                        <q-input v-model="form.direccion" label="Dirección Exacta *" outlined dense :rules="[val => !!val || 'Requerida']" />
                                    </div>
                                </div>
                            </q-card>
                        </div>

                        <!-- SECCIÓN 4: CLINICOS -->
                        <div class="q-mb-md">
                            <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">
                                <q-icon name="clinical_notes" class="q-mr-xs" />
                                Datos Clínicos
                            </div>
                            <q-card flat bordered class="q-pa-md bg-white">
                                <div class="row q-col-gutter-md">
                                    <div class="col-12">
                                        <q-input v-model="form.diagnostico" label="Diagnóstico *" outlined dense type="textarea" rows="2" :rules="[val => !!val || 'Requerido']" />
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <q-input v-model="form.indicaciones" label="Indicaciones" outlined dense type="textarea" rows="2" />
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <q-input v-model="form.observaciones" label="Observaciones Administrativas" outlined dense type="textarea" rows="2" />
                                    </div>
                                </div>
                            </q-card>
                        </div>

                    </q-form>
                </q-card-section>

                <q-separator />

                <q-card-actions align="right" class="q-pa-md bg-white">
                    <q-btn flat label="Cerrar" color="grey-7" v-close-popup />
                    <q-btn v-if="!readonly" label="Guardar Prescripción" color="primary" icon="save" :loading="saving" @click="save" unelevated />
                </q-card-actions>
            </q-card>

            <!-- Dialogos Anidados -->
            <PacienteSelector 
                v-model="showPacienteSelector" 
                @select="selectPaciente"
            />

            <!-- Nuevo Dialogo de Pacientes -->
            <PacientesDialog
                v-model="showNewPacienteDialog"
                @saved="selectPaciente"
            />
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useQuasar, date } from "quasar";
import axios from "axios";
import PacienteSelector from './PacienteSelector.vue';
// Import relativo al modulo de pacientes
import PacientesDialog from '../../Pacientes/Components/PacientesDialog.vue'; 

const props = defineProps({
    modelValue: Boolean,
    rowToEdit: Object,
    readonly: {
        type: Boolean,
        default: false
    }
});
const emit = defineEmits(['update:modelValue', 'saved']);

const $q = useQuasar();
const showPacienteSelector = ref(false);
const showNewPacienteDialog = ref(false);

const dialog = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const form = ref({
    id: null,
    numero: '', // Manual
    paciente_id: null,
    empleado_id: null,
    producto_id: null,
    cantidad_total: 1,
    numero_entregas: 1,
    fecha_prescripcion: date.formatDate(new Date(), 'YYYY-MM-DD'),
    fecha_vencimiento: '',
    ciudad: '',
    municipio: '',
    barrio: '',
    direccion: '',
    diagnostico: '',
    indicaciones: '',
    observaciones: ''
});

const selectedPaciente = ref({});
const empleados = ref([]);
const productos = ref([]);
const empleadosOriginal = ref([]);
const productosOriginal = ref([]);
const saving = ref(false);

const visitadorAsignado = ref('');

const loadEmpleados = async () => {
    try {
        const res = await axios.get('/api/empleados', {
            params: {
                cargo_codigo: 'PROFESIONAL_SALUD',
                include_visitadores: true
            }
        }); 
        empleadosOriginal.value = res.data.data || res.data;
        empleados.value = empleadosOriginal.value;
    } catch(e) {}
};

const filterEmpleados = (val, update) => {
    if (val === '') { update(() => { empleados.value = empleadosOriginal.value }); return; }
    update(() => {
        const needle = val.toLowerCase();
        empleados.value = empleadosOriginal.value.filter(v => 
            v.nombres.toLowerCase().indexOf(needle) > -1 || 
            v.apellidos.toLowerCase().indexOf(needle) > -1
        );
    });
};

// ... loadProductos y filters ...

// Observar cambio de profesional para asignar visitador
watch(() => form.value.empleado_id, (val) => {
    visitadorAsignado.value = '';
    if (val) {
        const emp = empleadosOriginal.value.find(e => e.id === val);
        if (emp && emp.visitadores_asignados && emp.visitadores_asignados.length > 0) {
            const vis = emp.visitadores_asignados[0];
            visitadorAsignado.value = `${vis.nombres} ${vis.apellidos}`;
        } else {
            visitadorAsignado.value = 'Sin visitador asignado';
        }
    }
});

const loadProductos = async () => {
    try {
        const res = await axios.get('/api/productos'); 
        productosOriginal.value = res.data.data || res.data;
        productos.value = productosOriginal.value;
    } catch(e) {}
};

const filterProductos = (val, update) => {
    if (val === '') { update(() => { productos.value = productosOriginal.value }); return; }
    update(() => {
        const needle = val.toLowerCase();
        productos.value = productosOriginal.value.filter(v => 
            v.nombre.toLowerCase().indexOf(needle) > -1 || 
            v.codigo.toLowerCase().indexOf(needle) > -1
        );
    });
};

const selectPaciente = (paciente) => {
    selectedPaciente.value = paciente;
    form.value.paciente_id = paciente.id;
    
    // Auto-fill ubicación
    // Intenta mapear campos del paciente a la prescripcion
    form.value.ciudad = paciente.departamento || ''; 
    form.value.municipio = paciente.municipio || '';
    form.value.barrio = paciente.ciudad || ''; // Nota: En formulario paciente 'ciudad' almacena el barrio
    form.value.direccion = paciente.direccion || '';
    
    showPacienteSelector.value = false;
    showNewPacienteDialog.value = false;
};

const clearPaciente = () => {
    selectedPaciente.value = {};
    form.value.paciente_id = null;
    form.value.ciudad = '';
    form.value.municipio = '';
    form.value.barrio = '';
    form.value.direccion = '';
};

const save = async () => {
    if (!form.value.paciente_id) {
        $q.notify({ type: 'warning', message: 'Seleccione un paciente' });
        return;
    }
    saving.value = true;
    try {
        if (form.value.id) {
            await axios.put(`/api/prescripciones/${form.value.id}`, form.value);
            $q.notify({ type: 'positive', message: 'Prescripción actualizada' });
        } else {
            await axios.post('/api/prescripciones', form.value);
            $q.notify({ type: 'positive', message: 'Prescripción creada' });
        }
        emit('saved');
        dialog.value = false;
    } catch (error) {
        console.error(error);
        $q.notify({ type: 'negative', message: 'Error al guardar Prescripción' });
    } finally {
        saving.value = false;
    }
};

// Watch para detectar cierre y asegurar limpieza
watch(() => props.modelValue, (val) => {
    if (!val) {
        // Al cerrar, resetear form y paciente select
        form.value = {
            id: null,
            numero: '',
            paciente_id: null,
            empleado_id: null,
            producto_id: null,
            cantidad_total: 1,
            numero_entregas: 1,
            fecha_prescripcion: date.formatDate(Date.now(), 'YYYY-MM-DD'),
            fecha_vencimiento: '',
            ciudad: '', municipio: '', barrio: '', direccion: '',
            diagnostico: '', indicaciones: '', observaciones: ''
        };
        selectedPaciente.value = {};
    }
});

watch(() => props.rowToEdit, (val) => {
    if (val) {
        // Copiar datos con seguridad
        const data = { ...val };
        
        // Sanitizar fechas (solo si son strings e ISO)
        if (typeof data.fecha_prescripcion === 'string' && data.fecha_prescripcion.includes('T')) {
            data.fecha_prescripcion = data.fecha_prescripcion.split('T')[0];
        }
        if (typeof data.fecha_vencimiento === 'string' && data.fecha_vencimiento.includes('T')) {
            data.fecha_vencimiento = data.fecha_vencimiento.split('T')[0];
        }

        form.value = data;

        // Asegurarse de cargar paciente asociado
        selectedPaciente.value = val.paciente || {};
        // Asegurar IDs (a veces vienen como strings)
        form.value.paciente_id = val.paciente_id;
        form.value.empleado_id = val.empleado_id;
        form.value.producto_id = val.producto_id;

    } else {
        // Reset form
        form.value = {
            id: null,
            numero: '',
            paciente_id: null,
            empleado_id: null,
            producto_id: null,
            cantidad_total: 1,
            numero_entregas: 1,
            fecha_prescripcion: date.formatDate(Date.now(), 'YYYY-MM-DD'),
            fecha_vencimiento: '',
            ciudad: '', municipio: '', barrio: '', direccion: '',
            diagnostico: '', indicaciones: '', observaciones: ''
        };
        selectedPaciente.value = {};
    }
});


onMounted(() => {
    loadEmpleados();
    loadProductos();
});
</script>

<style scoped>
/* Ajustes visuales para el scroll */
.scroll {
    overflow-y: auto;
}
</style>
