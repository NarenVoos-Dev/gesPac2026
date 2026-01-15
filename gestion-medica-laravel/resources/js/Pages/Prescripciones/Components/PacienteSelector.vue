<template>
    <q-dialog v-model="show" persistent>
        <q-card style="min-width: 700px; max-width: 80vw" class="modern-dialog">
            <q-card-section class="bg-primary text-white row items-center q-pb-xs">
                <q-icon name="person_search" size="24px" class="q-mr-sm" />
                <div class="text-h6">Buscar Paciente</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup color="white" />
            </q-card-section>
            
            <q-card-section>
                <div class="row q-col-gutter-sm q-mb-md">
                    <div class="col">
                        <q-input
                            v-model="search"
                            outlined
                            dense
                            placeholder="Buscar por cédula o nombre..."
                            debounce="500"
                            clearable
                            @update:model-value="loadPacientes"
                            autofocus
                        >
                            <template v-slot:append>
                                <q-icon name="search" />
                            </template>
                        </q-input>
                    </div>
                </div>

                <q-table
                    :rows="pacientes"
                    :columns="columns"
                    row-key="id"
                    :loading="loading"
                    flat
                    bordered
                    dense
                    :pagination="{ rowsPerPage: 10 }"
                    class="cursor-pointer-row"
                    @row-click="(evt, row) => selectPaciente(row)"
                >
                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props" auto-width>
                            <q-btn 
                                flat 
                                round 
                                dense 
                                icon="check_circle" 
                                color="positive" 
                                @click.stop="selectPaciente(props.row)"
                            >
                                <q-tooltip>Seleccionar</q-tooltip>
                            </q-btn>
                        </q-td>
                    </template>
                </q-table>
            </q-card-section>
        </q-card>
    </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'select']);

const show = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const search = ref('');
const pacientes = ref([]);
const loading = ref(false);

const columns = [
    { name: 'documento', label: 'Documento', field: row => row.documento_numero, align: 'left', sortable: true },
    { name: 'nombre', label: 'Nombre Completo', field: row => row.nombre_completo || `${row.p_nombres} ${row.p_apellidos}`, align: 'left', sortable: true },
    { name: 'ubicacion', label: 'Ubicación', field: row => `${row.municipio || ''} - ${row.departamento || ''}`, align: 'left', sortable: true },
    { name: 'ciudad', label: 'Barrio/Sector', field: 'ciudad', align: 'left' },
    { name: 'actions', label: '', align: 'right' },
];

const loadPacientes = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/pacientes', {
            params: { search: search.value }
        });
        pacientes.value = Array.isArray(response.data) ? response.data : response.data.data;
    } catch (error) {
        console.error("Error cargando pacientes", error);
    } finally {
        loading.value = false;
    }
};

const selectPaciente = (paciente) => {
    emit('select', paciente);
    show.value = false;
};

// Cargar al abrir si está vacío
watch(show, (val) => {
    if (val && pacientes.value.length === 0) loadPacientes();
});
</script>

<style scoped>
.modern-dialog {
    border-radius: 8px;
    overflow: hidden;
}
.cursor-pointer-row :deep(tbody tr) {
    cursor: pointer;
    transition: background-color 0.2s;
}
.cursor-pointer-row :deep(tbody tr:hover) {
    background-color: #f5f5f5;
}
</style>
