<template>
    <Head title="Configuración" />

    <AuthenticatedLayout>
        <q-page class="friendly-page">
            <div class="page-wrapper">
                <!-- BARRA DE NAVEGACIÓN AMIGABLE -->
                <div class="navigation-bar">
                    <q-card flat bordered class="nav-card">
                        <q-card-section class="q-pa-xs">
                            <div class="nav-content">
                                <!-- BREADCRUMBS — PANTALLA GRANDE -->
                                <q-breadcrumbs class="large-screen-only friendly-breadcrumbs">
                                    <template #separator>
                                        <q-icon size="20px" name="chevron_right" color="primary" />
                                    </template>

                                    <q-breadcrumbs-el
                                        label="Inicio"
                                        icon="home"
                                        :href="route('dashboard')"
                                        class="breadcrumb-link"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Volver al inicio
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el
                                        label="Sistema"
                                        icon="settings"
                                        class="breadcrumb-link"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Módulo de Sistema
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el
                                        label="Configuración"
                                        icon="tune"
                                        class="breadcrumb-current"
                                    />
                                </q-breadcrumbs>

                                <!-- BREADCRUMBS — PANTALLA PEQUEÑA -->
                                <q-breadcrumbs class="small-screen-only friendly-breadcrumbs-mobile">
                                    <template #separator>
                                        <q-icon size="18px" name="chevron_right" color="primary" />
                                    </template>

                                    <q-breadcrumbs-el 
                                        icon="home" 
                                        :href="route('dashboard')"
                                        class="mobile-breadcrumb"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Inicio
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el 
                                        icon="settings" 
                                        class="mobile-breadcrumb"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Sistema
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el 
                                        icon="tune"
                                        class="mobile-breadcrumb active"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Configuración
                                        </q-tooltip>
                                    </q-breadcrumbs-el>
                                </q-breadcrumbs>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- CONTENIDO PRINCIPAL CON TABS -->
                <div class="content-container">
                    <q-card flat bordered class="config-card">
                        <q-tabs
                            v-model="tab"
                            dense
                            class="text-grey-7"
                            active-color="primary"
                            indicator-color="primary"
                            align="left"
                        >
                            <q-tab name="tipos_documento" icon="badge" label="Tipos de Documento" />
                            <q-tab name="cargos" icon="work" label="Cargos" />
                            <q-tab name="especialidades" icon="medical_services" label="Especialidades" />
                            <q-tab name="empleados" icon="people" label="Empleados" />
                            <q-tab name="asignaciones" icon="account_tree" label="Asignaciones" />
                            <q-tab name="productos" icon="inventory_2" label="Productos" />
                            <q-tab name="comisiones" icon="payments" label="Conf. Comisiones" />
                            <q-tab name="categorias" icon="category" label="Categorías" />
                        </q-tabs>

                        <q-separator />

                        <q-tab-panels v-model="tab" animated>
                            <q-tab-panel name="tipos_documento">
                                <TiposDocumento />
                            </q-tab-panel>

                            <q-tab-panel name="cargos">
                                <Cargos />
                            </q-tab-panel>

                            <q-tab-panel name="especialidades">
                                <Especialidades />
                            </q-tab-panel>

                            <q-tab-panel name="empleados">
                                <Empleados />
                            </q-tab-panel>

                            <q-tab-panel name="asignaciones">
                                <AsignacionesComponent />
                            </q-tab-panel>

                            <q-tab-panel name="productos" class="q-pa-none">
                                <ProductosComponent />
                            </q-tab-panel>

                            <q-tab-panel name="comisiones" class="q-pa-none">
                                <ComisionesComponent />
                            </q-tab-panel>

                            <q-tab-panel name="categorias">
                                <div class="text-center q-pa-xl">
                                    <q-icon name="construction" size="64px" color="grey-5" />
                                    <div class="text-h6 text-grey-6 q-mt-md">Próximamente</div>
                                </div>
                            </q-tab-panel>
                        </q-tab-panels>
                    </q-card>
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TiposDocumento from './Components/TiposDocumentoComponent.vue';
import Cargos from './Components/CargosComponent.vue';
import Especialidades from './Components/EspecialidadesComponent.vue';
import Empleados from './Components/EmpleadosComponent.vue';
import AsignacionesComponent from './Components/AsignacionesComponent.vue';
import ProductosComponent from "@/Pages/Configuracion/Components/ProductosComponent.vue";
import ComisionesComponent from "@/Pages/Configuracion/Components/ComisionesComponent.vue";
import { Head } from '@inertiajs/vue3';

const tab = ref('tipos_documento');
</script>

<style scoped>
/* Página amigable */
.friendly-page {
    background: #F8F9FA;
}

.page-wrapper {
    width: 100%;
    margin: 0 auto;
    padding: 0;
}

/* Barra de navegación */
.navigation-bar {
    margin-bottom: 24px;
}

.nav-card {
    border: 1px solid #E9ECEF;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.nav-content {
    display: flex;
    align-items: center;
}

/* Breadcrumbs grandes */
.friendly-breadcrumbs {
    font-size: 14px;
}

.breadcrumb-link {
    color: #4CAF50;
    transition: all 0.2s ease;
    cursor: pointer;
}

.breadcrumb-link:hover {
    color: #66BB6A;
    transform: translateX(2px);
}

.breadcrumb-current {
    color: #6C757D;
    font-weight: 500;
}

/* Breadcrumbs móviles */
.friendly-breadcrumbs-mobile {
    font-size: 13px;
}

.mobile-breadcrumb {
    color: #4CAF50;
    transition: all 0.2s ease;
}

.mobile-breadcrumb.active {
    color: #6C757D;
}

/* Tooltips amigables */
.friendly-tooltip {
    background: #37474F;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 6px;
}

/* Contenedor de contenido */
.content-container {
    min-height: 400px;
}

/* Card de configuración */
.config-card {
    border: 1px solid #E9ECEF;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Responsive */
.large-screen-only {
    display: flex;
}

.small-screen-only {
    display: none;
}

@media (max-width: 768px) {
    .large-screen-only {
        display: none;
    }
    
    .small-screen-only {
        display: flex;
    }
    
    .page-wrapper {
        padding: 0;
    }
}

/* Deep selectors para iconos de breadcrumbs */
:deep(.q-breadcrumbs__el-icon) {
    font-size: 18px;
}

:deep(.q-breadcrumbs__separator) {
    margin: 0 8px;
}

/* Tabs personalizados */
:deep(.q-tab) {
    font-weight: 500;
}

:deep(.q-tab__label) {
    font-size: 13px;
}

/* Tab panels más compactos */
:deep(.q-tab-panel) {
    padding: 12px;
}
</style>
