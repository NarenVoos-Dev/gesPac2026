<template>
    <q-dialog v-model="show" persistent transition-show="scale" transition-hide="scale" maximized>
        <q-card class="modern-dialog">
            <q-card-section class="row items-center q-pb-none bg-primary text-white">
                <q-icon :name="readOnly ? 'visibility' : 'person_add'" size="24px" class="q-mr-sm" />
                <div class="text-h6">{{ readOnly ? 'Ver' : (form.id ? 'Editar' : 'Nuevo') }} Paciente</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup color="white" />
            </q-card-section>

            <q-separator />

            <q-card-section class="q-pt-xs" style="max-height: calc(100vh - 120px); overflow-y: auto;">
                <q-form @submit="save" class="q-gutter-xs">
                    <!-- SECCIÓN: INFORMACIÓN PERSONAL -->
                    <div class="text-subtitle2 text-grey-8 q-mt-xs q-mb-xs">
                        <q-icon name="person" class="q-mr-xs" /> Información Personal
                    </div>
                    <q-separator class="q-mb-md" />

                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-3">
                            <q-input v-model="form.p_nombres" label="Primer Nombre *" outlined dense :readonly="readOnly" :rules="[val => !!val || 'Requerido']"><template v-slot:prepend><q-icon name="person" color="primary" size="xs" /></template></q-input>
                        </div>
                        <div class="col-12 col-md-3">
                            <q-input v-model="form.s_nombres" label="Segundo Nombre" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="person_outline" color="primary" size="xs" /></template></q-input>
                        </div>
                        <div class="col-12 col-md-3">
                            <q-input v-model="form.p_apellidos" label="Primer Apellido *" outlined dense :readonly="readOnly" :rules="[val => !!val || 'Requerido']"><template v-slot:prepend><q-icon name="person" color="primary" size="xs" /></template></q-input>
                        </div>
                        <div class="col-12 col-md-3">
                            <q-input v-model="form.s_apellidos" label="Segundo Apellido" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="person_outline" color="primary" size="xs" /></template></q-input>
                        </div>
                    </div>

                    <!-- SECCIÓN: DOCUMENTO -->
                    <div class="text-subtitle2 text-grey-8 q-mt-xs q-mb-xs">
                        <q-icon name="badge" class="q-mr-xs" /> Documento de Identidad
                    </div>
                    <q-separator class="q-mb-md" />
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-4">
                            <q-select v-model="form.tipo_documento_id" :options="tiposDocumento" option-value="id" option-label="nombre" emit-value map-options label="Tipo de Documento *" outlined dense :disable="readOnly" :rules="[val => !!val || 'Requerido']"><template v-slot:prepend><q-icon name="badge" color="primary" size="xs" /></template></q-select>
                        </div>
                        <div class="col-12 col-md-4">
                            <q-input v-model="form.documento_numero" label="Número de Documento *" outlined dense :readonly="readOnly" :rules="[val => !!val || 'Requerido']"><template v-slot:prepend><q-icon name="numbers" color="primary" size="xs" /></template></q-input>
                        </div>
                        <div class="col-12 col-md-4">
                            <q-input v-model="form.fecha_nacimiento" label="Fecha de Nacimiento *" type="date" outlined dense :readonly="readOnly" :rules="[val => !!val || 'Requerido']"><template v-slot:prepend><q-icon name="cake" color="primary" size="xs" /></template></q-input>
                        </div>
                    </div>
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-4">
                            <q-select v-model="form.sexo" :options="opcionesSexo" emit-value map-options label="Sexo *" outlined dense :disable="readOnly" :rules="[val => !!val || 'Requerido']"><template v-slot:prepend><q-icon name="wc" color="primary" size="xs" /></template></q-select>
                        </div>
                    </div>

                    <!-- SECCIÓN: CONTACTO -->
                    <div class="text-subtitle2 text-grey-8 q-mt-xs q-mb-xs"><q-icon name="contact_phone" class="q-mr-xs" /> Información de Contacto</div>
                    <q-separator class="q-mb-md" />
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-4"><q-input v-model="form.telefono" label="Teléfono" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="phone" color="primary" size="xs" /></template></q-input></div>
                        <div class="col-12 col-md-4"><q-input v-model="form.email" label="Correo Electrónico" type="email" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="email" color="primary" size="xs" /></template></q-input></div>
                        <div class="col-12 col-md-4"><q-input v-model="form.direccion" label="Dirección" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="home" color="primary" size="xs" /></template></q-input></div>
                    </div>

                    <!-- SECCIÓN: UBICACIÓN -->
                    <div class="text-subtitle2 text-grey-8 q-mt-md q-mb-sm"><q-icon name="location_on" class="q-mr-xs" /> Ubicación</div>
                    <q-separator class="q-mb-md" />
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-3">
                            <q-select v-model="form.departamento" :options="departamentos" option-value="name" option-label="name" emit-value map-options use-input input-debounce="0" @filter="filterDepartamentos" @update:model-value="onDepartamentoChange" label="Departamento" outlined dense :loading="loadingDepartamentos" :disable="readOnly"><template v-slot:prepend><q-icon name="map" color="primary" size="xs" /></template></q-select>
                        </div>
                        <div class="col-12 col-md-3">
                            <q-select v-model="form.municipio" :options="municipios" option-value="name" option-label="name" emit-value map-options use-input input-debounce="0" @filter="filterMunicipios" label="Municipio" outlined dense :loading="loadingMunicipios" :disable="readOnly || !form.departamento"><template v-slot:prepend><q-icon name="location_city" color="primary" size="xs" /></template></q-select>
                        </div>
                        <div class="col-12 col-md-3"><q-input v-model="form.ciudad" label="Barrio" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="apartment" color="primary" size="xs" /></template></q-input></div>
                        <div class="col-12 col-md-3"><q-input v-model="form.codigo_postal" label="Código Postal" outlined dense :readonly="readOnly"><template v-slot:prepend><q-icon name="markunread_mailbox" color="primary" size="xs" /></template></q-input></div>
                    </div>

                    <!-- SECCIÓN: OBSERVACIONES -->
                    <div class="text-subtitle2 text-grey-8 q-mt-md q-mb-sm"><q-icon name="notes" class="q-mr-xs" /> Observaciones</div>
                    <q-separator class="q-mb-md" />
                    <q-input v-model="form.observaciones" label="Observaciones" type="textarea" outlined rows="3" :readonly="readOnly" />

                    <!-- Estado -->
                    <div class="q-mt-md">
                        <q-toggle v-model="form.is_active" label="Activo" color="positive" size="md" :disable="readOnly" />
                        <q-space />
                        <q-chip :color="form.is_active ? 'positive' : 'negative'" text-color="white" size="sm">{{ form.is_active ? 'Activo' : 'Inactivo' }}</q-chip>
                    </div>
                </q-form>
            </q-card-section>

            <q-separator />

            <q-card-actions align="right" class="q-pa-md">
                <q-btn v-if="!readOnly" label="Cancelar" color="grey-7" flat v-close-popup icon="close" />
                <q-btn v-if="!readOnly" label="Guardar" color="primary" unelevated :loading="saving" @click="save" icon="save" />
                <q-btn v-if="readOnly" label="Cerrar" color="primary" flat v-close-popup icon="close" />
            </q-card-actions>
        </q-card>
    </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import departamentosData from '@/json/departments.json';
import ciudadesData from '@/json/cities.json';

const props = defineProps(['modelValue', 'rowToEdit', 'readonlyMode']);
const emit = defineEmits(['update:modelValue', 'saved']);

const $q = useQuasar();
const show = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const saving = ref(false);
const readOnly = computed(() => props.readonlyMode);
const tiposDocumento = ref([]); // Cargar en mounted si no vienen de prop

// Form
const form = ref({
    id: null,
    p_nombres: '', s_nombres: '', p_apellidos: '', s_apellidos: '',
    tipo_documento_id: null, documento_numero: '', fecha_nacimiento: '',
    sexo: '', telefono: '', email: '', direccion: '',
    departamento: '', municipio: '', ciudad: '', codigo_postal: '',
    observaciones: '', is_active: true,
});

const opcionesSexo = [{ label: 'Masculino', value: 'M' }, { label: 'Femenino', value: 'F' }, { label: 'Otro', value: 'Otro' }];

// API Colombia
const departamentos = ref([]);
const departamentosOriginal = ref([]);
const municipios = ref([]);
const municipiosOriginal = ref([]);
const loadingDepartamentos = ref(false);
const loadingMunicipios = ref(false);

const loadLocalData = () => {
    try {
        loadingDepartamentos.value = true;
        // Acceder a la propiedad .data del JSON
        const deps = departamentosData.data || departamentosData;
        departamentosOriginal.value = deps;
        departamentos.value = deps;
    } catch (e) {
        console.error("Error cargando departamentos", e);
    } finally {
        loadingDepartamentos.value = false;
    }
};

const filterDepartamentos = (val, update) => {
    if (val === '') { update(() => { departamentos.value = departamentosOriginal.value }); return; }
    update(() => {
        const needle = val.toLowerCase();
        departamentos.value = departamentosOriginal.value.filter(v => v.name.toLowerCase().indexOf(needle) > -1);
    });
};

const onDepartamentoChange = () => {
    form.value.municipio = null;
    municipios.value = [];
    if (!form.value.departamento) return;
    
    const depObj = departamentosOriginal.value.find(d => d.name === form.value.departamento);
    if (depObj) {
        // Acceder a .data de ciudades y usar la propiedad correcta 'departmentId'
        const allCities = ciudadesData.data || ciudadesData;
        municipiosOriginal.value = allCities.filter(c => c.departmentId === depObj.id);
        municipios.value = municipiosOriginal.value;
    }
};

const filterMunicipios = (val, update) => {
    if (val === '') { update(() => { municipios.value = municipiosOriginal.value }); return; }
    update(() => {
        const needle = val.toLowerCase();
        municipios.value = municipiosOriginal.value.filter(v => v.name.toLowerCase().indexOf(needle) > -1);
    });
};

const loadTiposDocumento = async () => {
    // Mock o API call? Asumir API si existe
    // User code usa this.tiposDocumento pero no vi la carga.
    // Usaré un mock o API standard. 
    // Tipos doc: CC, TI, CE, Pasaporte.
    // Si hay endpoint: /api/tipos-documento
    /* try {
        const res = await axios.get('/api/tipos-documento'); 
        tiposDocumento.value = res.data; 
    } catch(e) { */
        tiposDocumento.value = [
            { id: 1, nombre: 'Cédula de Ciudadanía' }, 
            { id: 2, nombre: 'Tarjeta de Identidad' },
            { id: 3, nombre: 'Cédula de Extranjería' },
            { id: 4, nombre: 'Pasaporte' }
        ]; 
    /* } */
};

const save = async () => {
    saving.value = true;
    try {
        let res;
        if (form.value.id) {
            res = await axios.put(`/api/pacientes/${form.value.id}`, form.value);
            $q.notify({ type: 'positive', message: 'Paciente actualizado' });
        } else {
            res = await axios.post('/api/pacientes', form.value);
            $q.notify({ type: 'positive', message: 'Paciente creado' });
        }
        emit('saved', res.data.data || res.data); // data suele venir en wrap
        show.value = false;
    } catch (error) {
        console.error(error);
        $q.notify({ type: 'negative', message: 'Error al guardar' });
    } finally {
        saving.value = false;
    }
};

watch(() => props.rowToEdit, (val) => {
    if (val) {
        form.value = { ...val }; // Copia profunda idealmente
        // Trigger load municipios si tiene departamento
        if (form.value.departamento) {
            // Reencontrar municipios
             const depObj = departamentosOriginal.value.find(d => d.name === form.value.departamento);
             if (depObj) {
                 municipiosOriginal.value = ciudadesData.filter(c => c.department_id === depObj.id);
                 municipios.value = municipiosOriginal.value;
             }
        }
    } else {
        // Reset
        form.value = {
            id: null, p_nombres: '', s_nombres: '', p_apellidos: '', s_apellidos: '',
            tipo_documento_id: null, documento_numero: '', fecha_nacimiento: '',
            sexo: '', telefono: '', email: '', direccion: '',
            departamento: '', municipio: '', ciudad: '', codigo_postal: '',
            observaciones: '', is_active: true,
        };
        municipios.value = [];
    }
}, { immediate: true });

onMounted(() => {
    loadLocalData();
    loadTiposDocumento();
});
</script>

<style scoped>
.modern-dialog {
    border-radius: 8px;
    overflow: hidden;
    min-width: 600px;
}
</style>
