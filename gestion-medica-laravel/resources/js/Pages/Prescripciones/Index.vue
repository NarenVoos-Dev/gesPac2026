<template>
    <Head title="Prescripciones" />

    <AuthenticatedLayout>
        <q-page class="friendly-page">
            <div class="page-wrapper">
                <!-- BARRA DE NAVEGACIÓN -->
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
                                        label="Gestión Médica"
                                        icon="medical_services"
                                        class="breadcrumb-link"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Módulo Clínico
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el
                                        label="Prescripciones"
                                        icon="assignment"
                                        class="breadcrumb-current"
                                    />
                                </q-breadcrumbs>

                                <!-- BREADCRUMBS — PANTALLA PEQUEÑA (Móvil) -->
                                <q-breadcrumbs class="small-screen-only friendly-breadcrumbs-mobile">
                                    <template #separator>
                                        <q-icon size="18px" name="chevron_right" color="primary" />
                                    </template>
                                    <q-breadcrumbs-el icon="home" :href="route('dashboard')" class="mobile-breadcrumb" />
                                    <q-breadcrumbs-el icon="assignment" class="mobile-breadcrumb active" />
                                </q-breadcrumbs>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- CONTENIDO PRINCIPAL -->
                <div class="content-container">
                    <q-card flat bordered class="content-card">
                        <q-card-section class="q-pa-md">
                            <PrescripcionesComponent />
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrescripcionesComponent from './Components/PrescripcionesComponent.vue';
import { Head } from '@inertiajs/vue3';
</script>

<style scoped>
/* Estilos consistentes con Pacientes/Index.vue */
.friendly-page { background: #F8F9FA; }
.page-wrapper { width: 100%; margin: 0 auto; }
.navigation-bar { margin-bottom: 24px; }
.nav-card, .content-card { border: 1px solid #E9ECEF; background: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
.nav-content { display: flex; align-items: center; }
.friendly-breadcrumbs { font-size: 14px; }
.breadcrumb-link { color: #4CAF50; transition: all 0.2s ease; cursor: pointer; }
.breadcrumb-link:hover { color: #66BB6A; transform: translateX(2px); }
.breadcrumb-current { color: #6C757D; font-weight: 500; }

.large-screen-only { display: flex; }
.small-screen-only { display: none; }
@media (max-width: 768px) {
    .large-screen-only { display: none; }
    .small-screen-only { display: flex; }
}

:deep(.q-breadcrumbs__el-icon) { font-size: 18px; }
:deep(.q-breadcrumbs__separator) { margin: 0 8px; }
</style>