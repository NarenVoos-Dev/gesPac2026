<template>
    <div>
        <StandardTable
            title="Listado de Prescripciones"
            :columns="columns"
            :rows="prescripciones"
            :loading="loading"
            :filter="filter"
            @request="loadPrescripciones"
            @new="openDialog(null)"
            new-button-label="Nueva Prescripción"
        >
             <template v-slot:body-cell-fecha_prescripcion="props">
                <q-td :props="props">
                    <div>{{ formatDateSimple(props.row.fecha_prescripcion) }}</div>
                </q-td>
            </template>
            <template v-slot:body-cell-paciente="props">
                <q-td :props="props">
                    <div class="text-weight-bold"><span class="text-caption text-grey">{{ props.row.paciente?.documento_numero }} - </span> {{ props.row.paciente?.nombre_completo }}</div>
                </q-td>
            </template>
            <template v-slot:body-cell-producto="props">
                <q-td :props="props">
                    <div>{{ props.row.producto?.nombre }}</div>
                </q-td>
            </template>
            <template v-slot:body-cell-cantidad="props">
                <q-td :props="props">
                    <q-badge color="grey-3" text-color="black" :label="`${props.row.cantidad_total} Unid.`" />
                </q-td>
            </template>
            <template v-slot:body-cell-estado="props">
                <q-td :props="props">
                    <q-chip 
                        :color="getStatusColor(props.row.estado)" 
                        text-color="white" 
                        dense 
                        size="sm"
                        :label="props.row.estado"
                    />
                </q-td>
            </template>    
            <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                    <q-btn flat round dense color="primary" icon="visibility" @click="view(props.row)">
                        <q-tooltip>Ver Detalles</q-tooltip>
                    </q-btn>
                    <q-btn flat round dense color="orange" icon="edit" @click="openDialog(props.row)" v-if="props.row.estado === 'ACTIVA'">
                        <q-tooltip>Editar</q-tooltip>
                    </q-btn>
                    <q-btn flat round dense color="negative" icon="delete" @click="anular(props.row)" v-if="props.row.estado === 'ACTIVA'">
                         <q-tooltip>Anular</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </StandardTable>

        <PrescripcionDialog
            v-model="dialog"
            :row-to-edit="editingRow"
            :readonly="isReadOnly"
            @saved="loadPrescripciones"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useQuasar, date } from "quasar";
import axios from "axios";
import StandardTable from "@/Components/Shared/StandardTable.vue";
import PrescripcionDialog from "./PrescripcionDialog.vue";

const props = defineProps({});
const $q = useQuasar();
const prescripciones = ref([]);
const loading = ref(false);
const filter = ref("");
const dialog = ref(false);
const editingRow = ref(null);
const isReadOnly = ref(false);

const columns = [
    { name: 'numero', label: 'Nº Prescripción', align: 'left', field: 'numero', sortable: true },
    { name: 'fecha_prescripcion', label: 'Fecha', align: 'left', field: 'fecha_prescripcion', sortable: true },
    { name: 'paciente', label: 'Paciente', align: 'left', field: row => row.paciente?.nombres, sortable: true },
    { name: 'producto', label: 'Producto', align: 'left', field: row => row.producto?.nombre, sortable: true },
    { name: 'cantidad', label: 'Cant.', align: 'center', field: 'cantidad_total', sortable: true },
    { name: 'empleado', label: 'Profesional', align: 'left', field: row => row.empleado ? `${row.empleado.nombres} ${row.empleado.apellidos}` : '', sortable: true },
    { name: 'estado', label: 'Estado', align: 'center', field: 'estado', sortable: true },
    { name: 'actions', label: 'Acciones', align: 'center' }
];

const formatDateSimple = (val) => {
    if (!val) return '';
    if (val.includes('T')) return val.split('T')[0];
    return val;
};

const loadPrescripciones = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/prescripciones', { params: { search: filter.value } });
        prescripciones.value = res.data;
    } catch (e) {
        console.error(e);
        $q.notify({ type: 'negative', message: 'Error cargando datos' });
    } finally {
        loading.value = false;
    }
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

const openDialog = (row) => {
    editingRow.value = row;
    isReadOnly.value = false;
    dialog.value = true;
};

const view = (row) => {
    editingRow.value = row;
    isReadOnly.value = true;
    dialog.value = true;
};

const anular = (row) => {
    $q.dialog({
        title: 'Confirmar Anulación',
        message: `¿Desea anular la prescripción ${row.numero}? Esto cancelará las entregas pendientes.`,
        class: 'modern-dialog',
        ok: { label: 'Anular', color: 'negative', unelevated: true },
        cancel: { label: 'Cancelar', color: 'grey-7', flat: true },
        persistent: true
    }).onOk(async () => {
        try {
            await axios.delete(`/api/prescripciones/${row.id}`);
            $q.notify({ type: 'positive', message: 'Anulada correctamente' });
            loadPrescripciones();
        } catch(e) {
            $q.notify({ type: 'negative', message: 'Error al anular' });
        }
    });
};

onMounted(() => {
    loadPrescripciones();
});
</script>
