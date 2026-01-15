<template>
    <div class="asignaciones-container">
        <q-tabs
            v-model="subTab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="right"
        >
            <q-tab
                name="agente_visitador"
                icon="supervisor_account"
                :label="`${labelAgente} → ${labelVisitador}`"
            />
            <q-tab
                name="visitador_profesional"
                icon="medical_services"
                :label="`${labelVisitador} → ${labelProfesional}`"
            />
        </q-tabs>

        <q-separator class="q-my-xs" />

        <q-tab-panels v-model="subTab" animated>
            <!-- TAB: Agente → Visitador -->
            <q-tab-panel name="agente_visitador">
                <StandardTable
                    :title="`Asignación de ${labelVisitador}s a ${labelAgente}s`"
                    :subtitle="`Gestiona qué ${labelVisitador.toLowerCase()}s están asignados a cada ${labelAgente.toLowerCase()}`"
                    :rows="agentes"
                    :columns="columnsAgentes"
                    :loading="loading"
                    row-key="id"
                    :show-new-button="false"
                >
                    <!-- COLUMNA: Agente -->
                    <template v-slot:body-cell-nombre_completo="props">
                        <q-td :props="props">
                            <div class="text-weight-medium">
                                {{ props.row.nombre_completo }}
                            </div>
                            <div class="text-caption text-grey-6">
                                {{ props.row.documento_numero }}
                            </div>
                        </q-td>
                    </template>

                    <!-- COLUMNA: Visitadores Asignados -->
                    <template v-slot:body-cell-visitadores_count="props">
                        <q-td :props="props">
                            <q-badge
                                color="blue"
                                :label="props.row.visitadores_count"
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
                                icon="link"
                                color="info"
                                @click="openAsignarVisitadoresDialog(props.row)"
                            >
                                <q-tooltip
                                    transition-show="rotate"
                                    transition-hide="rotate"
                                    class="bg-info text-body2"
                                    :offset="[10, 10]"
                                    >Gestionar {{ labelVisitador }}s</q-tooltip
                                >
                            </q-btn>
                        </q-td>
                    </template>
                </StandardTable>
            </q-tab-panel>

            <!-- TAB: Visitador → Profesional -->
            <q-tab-panel name="visitador_profesional">
                <StandardTable
                    :title="`Asignación de ${labelProfesional}es a ${labelVisitador}s`"
                    :subtitle="`Gestiona qué ${labelProfesional.toLowerCase()}es están asignados a cada ${labelVisitador.toLowerCase()}`"
                    :rows="visitadores"
                    :columns="columnsVisitadores"
                    :loading="loadingVisitadores"
                    row-key="id"
                    :show-new-button="false"
                >
                    <!-- COLUMNA: Visitador -->
                    <template v-slot:body-cell-nombre_completo="props">
                        <q-td :props="props">
                            <div class="text-weight-medium">
                                {{ props.row.nombre_completo }}
                            </div>
                            <div class="text-caption text-grey-6">
                                {{ props.row.documento_numero }}
                            </div>
                        </q-td>
                    </template>

                    <!-- COLUMNA: Profesionales Asignados -->
                    <template v-slot:body-cell-profesionales_count="props">
                        <q-td :props="props">
                            <q-badge
                                color="green"
                                :label="props.row.profesionales_count"
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
                                icon="link"
                                color="info"
                                @click="
                                    openAsignarProfesionalesDialog(props.row)
                                "
                            >
                                <q-tooltip
                                    transition-show="rotate"
                                    transition-hide="rotate"
                                    class="bg-info text-body2"
                                    :offset="[10, 10]"
                                    >Gestionar {{ labelProfesional }}es</q-tooltip
                                >
                            </q-btn>
                        </q-td>
                    </template>
                </StandardTable>
            </q-tab-panel>
        </q-tab-panels>

        <!-- DIALOG: Asignar Visitadores -->
        <q-dialog v-model="dialogAsignar" persistent>
            <q-card style="min-width: 500px">
                <q-card-section
                    class="row items-center q-pb-xs bg-info text-white"
                >
                    <q-icon name="link" size="24px" class="q-mr-sm" />
                    <div class="text-h6">
                        Gestionar {{ labelVisitador }}s
                    </div>
                    <q-space />
                    <q-btn
                        icon="close"
                        flat
                        round
                        dense
                        v-close-popup
                        color="white"
                    />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-md">
                        {{ labelAgente }}:
                        <strong>{{ selectedAgente?.nombre_completo }}</strong>
                    </div>

                    <q-input
                        v-model="searchVisitador"
                        :placeholder="`Buscar ${labelVisitador.toLowerCase()}...`"
                        outlined
                        dense
                        class="q-mb-md"
                    >
                        <template v-slot:prepend>
                            <q-icon name="search" />
                        </template>
                    </q-input>

                    <div class="text-caption text-grey-7 q-mb-sm">
                        Selecciona los {{ labelVisitador.toLowerCase() }}s que
                        estarán asignados a este {{ labelAgente.toLowerCase() }}:
                    </div>

                    <q-scroll-area style="height: 300px">
                        <q-list bordered separator>
                            <q-item
                                v-for="visitador in visitadoresFiltrados"
                                :key="visitador.id"
                                clickable
                                @click="toggleVisitador(visitador.id)"
                            >
                                <q-item-section side>
                                    <q-checkbox
                                        :model-value="
                                            visitadoresSeleccionados.includes(
                                                visitador.id
                                            )
                                        "
                                        @update:model-value="
                                            toggleVisitador(visitador.id)
                                        "
                                    />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label>{{
                                        visitador.nombre_completo
                                    }}</q-item-label>
                                    <q-item-label caption>{{
                                        visitador.documento_numero
                                    }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-scroll-area>

                    <div class="text-caption text-grey-7 q-mt-md">
                        Seleccionados:
                        <strong>{{ visitadoresSeleccionados.length }}</strong>
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
                        label="Guardar Asignaciones"
                        color="primary"
                        unelevated
                        :loading="saving"
                        @click="guardarAsignaciones"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- DIALOG: Asignar Profesionales -->
        <q-dialog v-model="dialogAsignarProfesionales" persistent>
            <q-card style="min-width: 500px">
                <q-card-section
                    class="row items-center q-pb-xs bg-info text-white"
                >
                    <q-icon name="link" size="24px" class="q-mr-sm" />
                    <div class="text-h6">
                        Gestionar {{ labelProfesional }}es
                    </div>
                    <q-space />
                    <q-btn
                        icon="close"
                        flat
                        round
                        dense
                        v-close-popup
                        color="white"
                    />
                </q-card-section>

                <q-separator />

                <q-card-section>
                    <div class="text-subtitle2 q-mb-md">
                        {{ labelVisitador }}:
                        <strong>{{
                            selectedVisitador?.nombre_completo
                        }}</strong>
                    </div>

                    <q-input
                        v-model="searchProfesional"
                        :placeholder="`Buscar ${labelProfesional.toLowerCase()}...`"
                        outlined
                        dense
                        class="q-mb-md"
                    >
                        <template v-slot:prepend>
                            <q-icon name="search" />
                        </template>
                    </q-input>

                    <div class="text-caption text-grey-7 q-mb-sm">
                        Selecciona los
                        {{ labelProfesional.toLowerCase() }}es que estarán
                        asignados a este {{ labelVisitador.toLowerCase() }}:
                    </div>

                    <q-scroll-area style="height: 300px">
                        <q-list bordered separator>
                            <q-item
                                v-for="profesional in profesionalesFiltrados"
                                :key="profesional.id"
                                clickable
                                @click="toggleProfesional(profesional.id)"
                            >
                                <q-item-section side>
                                    <q-checkbox
                                        :model-value="
                                            profesionalesSeleccionados.includes(
                                                profesional.id
                                            )
                                        "
                                        @update:model-value="
                                            toggleProfesional(profesional.id)
                                        "
                                    />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label>{{
                                        profesional.nombre_completo
                                    }}</q-item-label>
                                    <q-item-label caption>{{
                                        profesional.documento_numero
                                    }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-scroll-area>

                    <div class="text-caption text-grey-7 q-mt-md">
                        Seleccionados:
                        <strong>{{ profesionalesSeleccionados.length }}</strong>
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
                        label="Guardar Asignaciones"
                        color="primary"
                        unelevated
                        :loading="savingProfesionales"
                        @click="guardarAsignacionesProfesionales"
                    />
                </q-card-section>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";
import StandardTable from "@/Components/Shared/StandardTable.vue";

const $q = useQuasar();

// Estado
const subTab = ref("agente_visitador");
const agentes = ref([]);
const visitadoresDisponibles = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialogAsignar = ref(false);
const selectedAgente = ref(null);
const visitadoresSeleccionados = ref([]);
const searchVisitador = ref("");

// Estado para Visitador → Profesional
const visitadores = ref([]);
const profesionalesDisponibles = ref([]);
const loadingVisitadores = ref(false);
const savingProfesionales = ref(false);
const dialogAsignarProfesionales = ref(false);
const selectedVisitador = ref(null);
const profesionalesSeleccionados = ref([]);
const searchProfesional = ref("");

// Etiquetas dinámicas
const labelAgente = ref("Agente");
const labelVisitador = ref("Visitador");
const labelProfesional = ref("Profesional");

// Columnas de la tabla (Computed para reactividad de labels)
const columnsAgentes = computed(() => [
    {
        name: "nombre_completo",
        label: labelAgente.value,
        align: "left",
        field: "nombre_completo",
        sortable: true,
    },
    {
        name: "visitadores_count",
        label: `${labelVisitador.value}s Asignados`,
        align: "center",
        field: "visitadores_count",
        sortable: true,
    },
    {
        name: "acciones",
        label: "Acciones",
        align: "center",
    },
]);

const columnsVisitadores = computed(() => [
    {
        name: "nombre_completo",
        label: labelVisitador.value,
        align: "left",
        field: "nombre_completo",
        sortable: true,
    },
    {
        name: "profesionales_count",
        label: `${labelProfesional.value}es Asignados`,
        align: "center",
        field: "profesionales_count",
        sortable: true,
    },
    {
        name: "acciones",
        label: "Acciones",
        align: "center",
    },
]);

// Computed
const visitadoresFiltrados = computed(() => {
    if (!searchVisitador.value) {
        return visitadoresDisponibles.value;
    }
    const search = searchVisitador.value.toLowerCase();
    return visitadoresDisponibles.value.filter(
        (v) =>
            v.nombre_completo.toLowerCase().includes(search) ||
            v.documento_numero.includes(search)
    );
});

const profesionalesFiltrados = computed(() => {
    if (!searchProfesional.value) {
        return profesionalesDisponibles.value;
    }
    const search = searchProfesional.value.toLowerCase();
    return profesionalesDisponibles.value.filter(
        (p) =>
            p.nombre_completo.toLowerCase().includes(search) ||
            p.documento_numero.includes(search)
    );
});

// Métodos
const loadAgentes = async () => {
    loading.value = true;
    try {
        const response = await axios.get("/api/asignaciones/agentes");
        agentes.value = response.data;
    } catch (error) {
        console.error("Error al cargar agentes:", error);
        $q.notify({
            type: "negative",
            message: "Error al cargar agentes",
        });
    } finally {
        loading.value = false;
    }
};

const loadVisitadoresDisponibles = async () => {
    try {
        const response = await axios.get(
            "/api/asignaciones/visitadores-disponibles"
        );
        visitadoresDisponibles.value = response.data;
    } catch (error) {
        console.error("Error al cargar visitadores:", error);
    }
};

const openAsignarVisitadoresDialog = async (agente) => {
    selectedAgente.value = agente;
    searchVisitador.value = "";

    // Cargar visitadores asignados actualmente
    try {
        const response = await axios.get(
            `/api/asignaciones/agentes/${agente.id}/visitadores`
        );
        visitadoresSeleccionados.value = response.data.map((v) => v.id);
    } catch (error) {
        console.error("Error al cargar visitadores asignados:", error);
        visitadoresSeleccionados.value = [];
    }

    dialogAsignar.value = true;
};

const toggleVisitador = (visitadorId) => {
    const index = visitadoresSeleccionados.value.indexOf(visitadorId);
    if (index > -1) {
        visitadoresSeleccionados.value.splice(index, 1);
    } else {
        visitadoresSeleccionados.value.push(visitadorId);
    }
};

const guardarAsignaciones = async () => {
    saving.value = true;
    try {
        await axios.post(
            `/api/asignaciones/agentes/${selectedAgente.value.id}/visitadores`,
            {
                visitadores: visitadoresSeleccionados.value,
            }
        );

        $q.notify({
            type: "positive",
            message: "Asignaciones guardadas exitosamente",
        });

        dialogAsignar.value = false;
        loadAgentes();
    } catch (error) {
        const message =
            error.response?.data?.message || "Error al guardar asignaciones";
        $q.notify({
            type: "negative",
            message: message,
        });
    } finally {
        saving.value = false;
    }
};

// Métodos para Visitador → Profesional
const loadVisitadores = async () => {
    loadingVisitadores.value = true;
    try {
        const response = await axios.get("/api/asignaciones/visitadores");
        visitadores.value = response.data;
    } catch (error) {
        console.error("Error al cargar visitadores:", error);
        $q.notify({
            type: "negative",
            message: "Error al cargar visitadores",
        });
    } finally {
        loadingVisitadores.value = false;
    }
};

const loadProfesionalesDisponibles = async () => {
    try {
        const response = await axios.get(
            "/api/asignaciones/profesionales-disponibles"
        );
        console.log(response.data);
        profesionalesDisponibles.value = response.data;
    } catch (error) {
        console.error("Error al cargar profesionales:", error);
    }
};

const openAsignarProfesionalesDialog = async (visitador) => {
    selectedVisitador.value = visitador;
    searchProfesional.value = "";

    // Cargar profesionales asignados actualmente
    try {
        const response = await axios.get(
            `/api/asignaciones/visitadores/${visitador.id}/profesionales`
        );
        profesionalesSeleccionados.value = response.data.map((p) => p.id);
    } catch (error) {
        console.error("Error al cargar profesionales asignados:", error);
        profesionalesSeleccionados.value = [];
    }

    dialogAsignarProfesionales.value = true;
};

const toggleProfesional = (profesionalId) => {
    const index = profesionalesSeleccionados.value.indexOf(profesionalId);
    if (index > -1) {
        profesionalesSeleccionados.value.splice(index, 1);
    } else {
        profesionalesSeleccionados.value.push(profesionalId);
    }
};

const guardarAsignacionesProfesionales = async () => {
    savingProfesionales.value = true;
    try {
        await axios.post(
            `/api/asignaciones/visitadores/${selectedVisitador.value.id}/profesionales`,
            {
                profesionales: profesionalesSeleccionados.value,
            }
        );

        $q.notify({
            type: "positive",
            message: "Asignaciones guardadas exitosamente",
        });

        dialogAsignarProfesionales.value = false;
        loadVisitadores();
    } catch (error) {
        const message =
            error.response?.data?.message || "Error al guardar asignaciones";
        $q.notify({
            type: "negative",
            message: message,
        });
    } finally {
        savingProfesionales.value = false;
    }
};

// Cargar etiquetas del sistema
const loadEtiquetas = async () => {
    try {
        const response = await axios.get("/api/cargos/etiquetas-sistema");
        labelAgente.value = response.data.AGENTE;
        labelVisitador.value = response.data.VISITADOR_MEDICO;
        // Ajuste para singular/plural si es necesario, o usar directamente lo que llega
        // "Profesional de la Salud" -> "Profesional" si se prefiere corto
        labelProfesional.value = response.data.PROFESIONAL_SALUD.split(" ")[0]; // "Profesional"
    } catch (error) {
        console.error("Error al cargar etiquetas:", error);
    }
};

// Lifecycle
onMounted(() => {
    loadEtiquetas();
    loadAgentes();
    loadVisitadoresDisponibles();
    loadVisitadores();
    loadProfesionalesDisponibles();
});
</script>

<style scoped>

.modern-card {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
