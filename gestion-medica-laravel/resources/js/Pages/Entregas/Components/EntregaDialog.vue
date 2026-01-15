<template>
    <q-dialog v-model="model" persistent>
        <q-card style="min-width: 400px; max-width: 500px">
            <q-card-section class="bg-primary text-white row items-center">
                <div class="text-h6">Gestionar Entrega #{{ entrega.numero_entrega }}</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section class="q-pt-md">
                <q-form @submit="onSubmit" class="q-gutter-md">
                    
                    <!-- Estado -->
                    <q-select
                        filled
                        v-model="form.estado"
                        :options="statusOptions"
                        label="Estado de la Entrega"
                        emit-value
                        map-options
                    >
                        <template v-slot:option="scope">
                            <q-item v-bind="scope.itemProps">
                                <q-item-section>
                                    <q-item-label>{{ scope.opt.label }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </template>
                    </q-select>

                    <!-- Cantidad Real (Solo si Entregado) -->
                    <q-input
                        v-if="form.estado === 'ENTREGADO'"
                        filled
                        type="number"
                        v-model.number="form.cantidad_entregada_real"
                        label="Cantidad Real Entregada"
                        :rules="[
                            val => val > 0 || 'Debe ser mayor a 0',
                            val => val <= maxCantidad || `Máximo permitido: ${maxCantidad}`
                        ]"
                    >
                        <template v-slot:hint>
                            Prescrito Total: {{ prescripcion.cantidad_total }} | Disponible: {{ maxCantidad }}
                        </template>
                    </q-input>

                    <!-- Próxima Fecha Validación (Solo si NO entregado) -->
                    <q-input
                        v-if="form.estado !== 'ENTREGADO' && form.estado !== 'CANCELADA'"
                        filled
                        v-model="form.proxima_fecha_validacion"
                        mask="date"
                        :rules="['date']"
                        label="Próxima Fecha de Seguimiento"
                    >
                        <template v-slot:append>
                            <q-icon name="event" class="cursor-pointer">
                                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                    <q-date v-model="form.proxima_fecha_validacion" :options="dateOptions" :locale="localeSpanish">
                                        <div class="row items-center justify-end">
                                            <q-btn v-close-popup label="Cerrar" color="primary" flat />
                                        </div>
                                    </q-date>
                                </q-popup-proxy>
                            </q-icon>
                        </template>
                    </q-input>

                    <!-- Observaciones -->
                    <q-input
                        filled
                        type="textarea"
                        v-model="form.observaciones"
                        label="Observaciones / Novedades"
                        rows="3"
                    />

                </q-form>
            </q-card-section>

            <q-card-section class="q-pt-none" v-if="entrega.observaciones_historial && entrega.observaciones_historial.length > 0">
                <div class="text-subtitle2 q-mb-sm text-grey-8">Historial de Observaciones</div>
                <q-list bordered separator density="compact" class="rounded-borders">
                    <q-item v-for="obs in entrega.observaciones_historial" :key="obs.id">
                        <q-item-section avatar top>
                            <q-avatar icon="person" color="primary" text-color="white" size="sm" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label class="text-caption text-weight-bold">{{ obs.user?.name }} - {{ formatDateFull(obs.created_at) }}</q-item-label>
                            <q-item-label caption lines="3" class="text-body2 text-black">{{ obs.observacion }}</q-item-label>
                        </q-item-section>
                    </q-item>
                </q-list>
            </q-card-section>

            <q-card-actions align="right" class="text-primary bg-grey-1">
                <q-btn flat label="Cancelar" v-close-popup color="grey" />
                <q-btn label="Guardar Cambios" color="primary" @click="onSubmit" :loading="loading" />
            </q-card-actions>
        </q-card>
    </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useQuasar, date } from 'quasar'; // Import date utils
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    entrega: Object,
    prescripcion: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);

const $q = useQuasar();
const loading = ref(false);

const model = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const formatDateFull = (val) => {
    return date.formatDate(val, 'DD/MM/YYYY HH:mm');
};

const dateOptions = (dateVal) => {
    return dateVal >= date.formatDate(Date.now(), 'YYYY/MM/DD');
};

const form = ref({
    estado: 'PENDIENTE',
    cantidad_entregada_real: 0,
    observaciones: '',
    proxima_fecha_validacion: null
});

const localeSpanish = {
  days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
  daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
  months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
  monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
  firstDayOfWeek: 1
};

const statusOptions = [
    { label: 'Pendiente', value: 'PENDIENTE' },
    { label: 'Programada', value: 'PROGRAMADA' },
    { label: 'En Ruta', value: 'EN_RUTA' },
    { label: 'Entregado', value: 'ENTREGADO' },
    { label: 'No Entregado / Fallida', value: 'NO_ENTREGADO' },
    { label: 'Cancelada', value: 'CANCELADA' }
];

// Calcular cantidad disponible basada en el total prescrito menos lo ya entregado (excluyendo esta entrega)
const maxCantidad = computed(() => {
    if (!props.prescripcion || !props.prescripcion.entregas) return 0;
    
    // Sumar entregas YA realizadas de otras entregas
    const deliveredOthers = props.prescripcion.entregas
        .filter(e => e.id !== props.entrega.id && e.entregado)
        .reduce((sum, e) => sum + (e.cantidad_entregada_real || 0), 0);
        
    return props.prescripcion.cantidad_total - deliveredOthers;
});

// Inicializar form al abrir
watch(() => props.entrega, (newVal) => {
    if (newVal) {
        form.value = {
            estado: newVal.estado,
            cantidad_entregada_real: newVal.cantidad_entregada_real || newVal.cantidad_programada, // Default a programada
            observaciones: '', //newVal.observaciones || '', 
            proxima_fecha_validacion: newVal.proxima_fecha_validacion // formatear si es necesario
        };
    }
}, { immediate: true });

const onSubmit = async () => {
    loading.value = true;
    try {
        const payload = {
            entregado: form.value.estado === 'ENTREGADO',
            estado: form.value.estado,
            cantidad_entregada_real: form.value.cantidad_entregada_real,
            observaciones: form.value.observaciones,
            proxima_fecha_validacion: form.value.proxima_fecha_validacion
        };

        const res = await axios.put(`/api/entregas/${props.entrega.id}`, payload);
        
        $q.notify({ type: 'positive', message: 'Entrega actualizada' });
        emit('saved', res.data.entrega);
        model.value = false;
    } catch (e) {
        console.error(e);
        const msg = e.response?.data?.message || 'Error al guardar';
        $q.notify({ type: 'negative', message: msg });
    } finally {
        loading.value = false;
    }
};
</script>
