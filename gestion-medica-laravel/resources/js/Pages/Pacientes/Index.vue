<template>
    <Head title="Pacientes" />

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
                                        label="Gestión"
                                        icon="business_center"
                                        class="breadcrumb-link"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Módulo de Gestión
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el
                                        label="Pacientes"
                                        icon="people"
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
                                        icon="business_center" 
                                        class="mobile-breadcrumb"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Gestión
                                        </q-tooltip>
                                    </q-breadcrumbs-el>

                                    <q-breadcrumbs-el 
                                        icon="people"
                                        class="mobile-breadcrumb active"
                                    >
                                        <q-tooltip class="friendly-tooltip" :offset="[0, 8]">
                                            Pacientes
                                        </q-tooltip>
                                    </q-breadcrumbs-el>
                                </q-breadcrumbs>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- CONTENIDO PRINCIPAL -->
                <div class="content-container">
                    <q-card flat bordered class="content-card">
                        <q-card-section class="q-pa-md">
                            <PacientesComponent />
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PacientesComponent from './Components/PacientesComponent.vue';
import { Head } from '@inertiajs/vue3';
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

/* Tarjeta de contenido */
.content-card {
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
</style>
