<template>
    <div>
        <StandardTable
            title="Gestión de Entregas y Despachos"
            :columns="columns"
            :rows="prescripciones"
            :loading="loading"
            v-model:filter="filter"
            @request="loadPrescripciones"
            :show-new-button="false"
        >
            <template v-slot:filters>
                <div class="row q-gutter-xs items-center">
                     <!-- Filtro Fecha Seguimiento -->
                    <q-input 
                        dense 
                        outlined 
                        v-model="filterDate" 
                        mask="date" 
                        label="Próx. Seguimiento"
                        class="q-mr-sm"
                        style="width: 180px"
                    >
                        <template v-slot:append>
                            <q-icon name="event" class="cursor-pointer">
                                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                    <q-date v-model="filterDate" @update:model-value="loadPrescripciones" :locale="localeSpanish">
                                        <div class="row items-center justify-end">
                                            <q-btn v-close-popup label="Cerrar" color="primary" flat />
                                        </div>
                                    </q-date>
                                </q-popup-proxy>
                            </q-icon>
                        </template>
                         <template v-slot:after v-if="filterDate">
                            <q-icon name="close" class="cursor-pointer" @click="filterDate = null; loadPrescripciones()" />
                        </template>
                    </q-input>
                </div>
            </template>

            <template v-slot:body="props">
                <q-tr :props="props">
                    <!-- Columna Expansión -->
                    <q-td auto-width>
                        <q-btn 
                            size="sm" 
                            color="primary" 
                            round 
                            dense 
                            @click="toggleExpand(props.row)" 
                            :icon="expandedRows.includes(props.row.id) ? 'remove' : 'add'" 
                        />
                    </q-td>

                    <q-td key="numero" :props="props">{{ props.row.numero }}</q-td>
                    <q-td key="fecha_prescripcion" :props="props">{{ formatDateSimple(props.row.fecha_prescripcion) }}</q-td>
                    <q-td key="paciente" :props="props">
                        <div class="text-weight-bold"><span class="text-caption text-grey">{{ props.row.paciente?.documento_numero }} - </span> {{ props.row.paciente?.nombre_completo }}</div>
                    </q-td>
                    <q-td key="producto" :props="props">{{ props.row.producto?.nombre }}</q-td>
                    <q-td key="cantidad" :props="props" class="text-center">
                        <q-badge color="grey-3" text-color="black" :label="`${props.row.cantidad_total} Unid.`" />
                    </q-td>
                     <q-td key="progreso" :props="props" class="text-center">
                        <!-- Mostrar cuántas entregas completadas de total -->
                         {{ props.row.entregas?.filter(e => e.entregado).length }} / {{ props.row.entregas?.length }}
                    </q-td>
                    <q-td key="estado" :props="props" class="text-center">
                        <q-chip :color="getStatusColor(props.row.estado)" text-color="white" dense size="sm" :label="props.row.estado" />
                    </q-td>
                </q-tr>

                <!-- FILA DE EXPANSIÓN (ENTREGAS) -->
                <q-tr v-show="expandedRows.includes(props.row.id)" :props="props">
                    <q-td colspan="100%">
                        <div class="q-pa-md bg-grey-1 rounded-borders">
                            <div class="text-subtitle2 text-primary q-mb-sm flex items-center justify-between">
                                <div class="flex items-center">
                                    <q-icon name="local_shipping" class="q-mr-xs"/>
                                    Cronograma de Entregas
                                </div>
                                <q-btn 
                                    round 
                                    dense 
                                    icon="add" 
                                    color="primary" 
                                    size="sm"
                                    @click="openAddDialog(props.row)"
                                >
                                    <q-tooltip>Agregar Entrega</q-tooltip>
                                </q-btn>
                            </div>
                            
                            <q-markup-table flat bordered dense class="bg-white">
                                <thead>
                                    <tr>
                                        <th class="text-left">Entrega</th>
                                        <th class="text-left">Programada</th>
                                        <th class="text-center">Cant. Prog.</th>
                                        <th class="text-center">Entregado</th>
                                        <th class="text-center">Cant. Real</th>
                                        <th class="text-left">Estado</th>
                                        <th class="text-left">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="entrega in props.row.entregas" :key="entrega.id">
                                        <td>#{{ entrega.numero_entrega }}</td>
                                        <td>{{ formatDateSimple(entrega.fecha_programada) }}</td>
                                        <td class="text-center">{{ entrega.cantidad_programada }}</td>
                                        <td class="text-center">
                                            <q-icon v-if="entrega.entregado" name="check_circle" color="positive" />
                                            <q-icon v-else name="pending" color="grey" />
                                        </td>
                                        <td class="text-center">
                                            {{ entrega.cantidad_entregada_real || '-' }}
                                        </td>
                                        <td>
                                            <q-badge :color="getEntregaStatusColor(entrega.estado)" :label="entrega.estado" />
                                            <div v-if="entrega.validador" class="text-caption text-grey">
                                                Valid: {{ entrega.validador.name }}
                                            </div>
                                            <div v-if="entrega.proxima_fecha_validacion" class="text-caption text-orange">
                                                Prox: {{ formatDateSimple(entrega.proxima_fecha_validacion) }}
                                            </div>
                                        </td>
                                        <td class="text-left">
                                            <q-btn 
                                                dense 
                                                flat 
                                                color="primary" 
                                                icon="settings" 
                                                label="Gestionar" 
                                                @click="openManageDialog(entrega, props.row)"
                                                size="sm"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </q-markup-table>
                        </div>
                    </q-td>
                </q-tr>
            </template>
        </StandardTable>

        <EntregaDialog 
            v-model="dialogOpen" 
            :entrega="selectedEntrega" 
            :prescripcion="selectedPrescripcion"
            @saved="onEntregaSaved"
        />

        <!-- Dialogo Crear Entrega (Simple) -->
        <q-dialog v-model="addDialogOpen" persistent>
            <q-card style="min-width: 300px">
                <q-card-section class="bg-primary text-white">
                    <div class="text-h6">Agregar Entrega Manual</div>
                </q-card-section>
                <q-card-section class="q-pt-md q-gutter-md">
                    <q-input 
                        filled 
                        v-model="newEntregaForm.fecha_programada" 
                        label="Fecha Programada" 
                        mask="date" 
                        :rules="['date']"
                    >
                         <template v-slot:append>
                            <q-icon name="event" class="cursor-pointer">
                                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                    <q-date v-model="newEntregaForm.fecha_programada" :locale="localeSpanish">
                                        <div class="row items-center justify-end">
                                            <q-btn v-close-popup label="Cerrar" color="primary" flat />
                                        </div>
                                    </q-date>
                                </q-popup-proxy>
                            </q-icon>
                        </template>
                    </q-input>
                    <q-input 
                        filled 
                        v-model.number="newEntregaForm.cantidad_programada" 
                        label="Cantidad Programada" 
                        type="number"
                        :rules="[val => val > 0 || 'Mínimo 1']"
                    />
                     <q-input 
                        filled 
                        v-model="newEntregaForm.observaciones" 
                        label="Motivo / Observación" 
                        type="textarea"
                        rows="2"
                    />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancelar" v-close-popup color="grey" />
                    <q-btn flat label="Agregar" color="primary" @click="saveNewEntrega" :loading="loadingAdd" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useQuasar, date } from "quasar";
import axios from "axios";
import StandardTable from "@/Components/Shared/StandardTable.vue";
import EntregaDialog from "./EntregaDialog.vue";

const $q = useQuasar();
const prescripciones = ref([]);
const loading = ref(false);
const filter = ref("");
const filterDate = ref(null); // Nuevo estado para fecha
const expandedRows = ref([]);

// Dialog States
const dialogOpen = ref(false);
const selectedEntrega = ref(null);
const selectedPrescripcion = ref(null);

const columns = [
    { name: 'expand', label: '', align: 'left', field: 'expand', sortable: false },
    { name: 'numero', label: 'Nº Prescripción', align: 'left', field: 'numero', sortable: true },
    { name: 'fecha_prescripcion', label: 'Fecha Solicitud', align: 'left', field: 'fecha_prescripcion', sortable: true },
    { name: 'paciente', label: 'Paciente', align: 'left', field: row => row.paciente?.nombres, sortable: true },
    { name: 'producto', label: 'Producto', align: 'left', field: row => row.producto?.nombre, sortable: true },
    { name: 'cantidad', label: 'Cant. Total', align: 'center', field: 'cantidad_total', sortable: true },
    { name: 'progreso', label: 'Entregas', align: 'center', field: 'progreso', sortable: false },
    { name: 'estado', label: 'Estado General', align: 'center', field: 'estado', sortable: true },
];

const formatDateSimple = (val) => {
    if (!val) return '';
    if (val.includes('T')) return val.split('T')[0];
    return val;
};

const loadPrescripciones = async () => {
    loading.value = true;
    try {
        const params = { 
            search: filter.value, 
            with_entregas: true,
            next_validation_date: filterDate.value // Enviar fecha filtrada
        };
        
        const res = await axios.get('/api/prescripciones', { params });
        prescripciones.value = res.data;
    } catch (e) {
        console.error(e);
        $q.notify({ type: 'negative', message: 'Error cargando datos' });
    } finally {
        loading.value = false;
    }
};

watch(filter, () => {
    loadPrescripciones();
});

const localeSpanish = {
  days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
  daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
  months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
  monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
  firstDayOfWeek: 1
};

const getStatusColor = (status) => {
    switch(status) {
        case 'ACTIVA': return 'positive';
        case 'COMPLETADA': return 'blue';
        case 'ANULADA': return 'negative';
        case 'VENCIDA': return 'orange';
        default: return 'grey';
    }
};

const getEntregaStatusColor = (status) => {
    switch(status) {
        case 'ENTREGADO': return 'positive';
        case 'PROGRAMADA': return 'info';
        case 'PENDIENTE': return 'grey-7';
        case 'NO_ENTREGADO': return 'negative';
        case 'EN_RUTA': return 'orange';
        default: return 'grey';
    }
};

const toggleExpand = (row) => {
    const index = expandedRows.value.indexOf(row.id);
    if (index > -1) {
        expandedRows.value.splice(index, 1);
    } else {
        expandedRows.value.push(row.id);
    }
};

const openManageDialog = (entrega, prescripcion) => {
    selectedEntrega.value = { ...entrega }; // Copia para no mutar directo
    selectedPrescripcion.value = prescripcion;
    dialogOpen.value = true;
};

const onEntregaSaved = (entregaActualizada) => {
    // Buscar la prescripción y la entrega en el array local y actualizarla
    // O simplemente recargar todo para asegurar consistencia
    if (selectedPrescripcion.value) {
        const pres = prescripciones.value.find(p => p.id === selectedPrescripcion.value.id);
        if (pres) {
            const entIndex = pres.entregas.findIndex(e => e.id === entregaActualizada.id);
            if (entIndex > -1) {
                pres.entregas[entIndex] = entregaActualizada;
            }
        }
    }
};

// Estado para Crear Entrega Manual
const addDialogOpen = ref(false);
const loadingAdd = ref(false);
const selectedPrescripcionForAdd = ref(null);
const newEntregaForm = ref({
    fecha_programada: '',
    cantidad_programada: '',
    observaciones: ''
});

const openAddDialog = (prescripcion) => {
    selectedPrescripcionForAdd.value = prescripcion;
    newEntregaForm.value = {
        fecha_programada: date.formatDate(Date.now(), 'YYYY/MM/DD'),
        cantidad_programada: '',
        observaciones: ''
    };
    addDialogOpen.value = true;
};

const saveNewEntrega = async () => {
    loadingAdd.value = true;
    try {
        const payload = {
            prescripcion_id: selectedPrescripcionForAdd.value.id,
            ...newEntregaForm.value
        };

        const res = await axios.post('/api/entregas', payload);
        
        $q.notify({ type: 'positive', message: 'Entrega agregada correctamente' });
        addDialogOpen.value = false;
        
        // Recargar datos para asegurar consistencia
        await loadPrescripciones();
    } catch (e) {
        console.error(e);
        const msg = e.response?.data?.message || 'Error al agregar entrega';
        $q.notify({ type: 'negative', message: msg });
    } finally {
        loadingAdd.value = false;
    }
};

onMounted(() => {
    loadPrescripciones();
});
</script>

<style scoped>
.input-cantidad {
    max-width: 80px;
}
</style>
