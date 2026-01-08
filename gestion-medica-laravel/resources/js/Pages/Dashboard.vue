<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <q-page padding class="modern-dashboard">
            <!-- Bienvenida -->
            <div class="q-mb-xl">
                <div class="text-h4 text-grey-9 text-weight-medium q-mb-xs">¡Bienvenido de nuevo!</div>
                <div class="text-body1 text-grey-6">Aquí tienes un resumen de tu sistema</div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row q-col-gutter-lg q-mb-xl">
                <div class="col-12 col-md-6 col-lg-3">
                    <q-card flat bordered class="stat-card stat-card-primary">
                        <q-card-section>
                            <div class="row items-center no-wrap">
                                <div class="col">
                                    <div class="text-overline text-grey-7">Pacientes</div>
                                    <div class="text-h3 text-weight-bold text-grey-9 q-mt-sm">0</div>
                                    <div class="text-caption text-positive q-mt-xs">
                                        <q-icon name="trending_up" size="14px" /> +0% este mes
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <q-icon name="people" size="56px" class="stat-icon stat-icon-primary" />
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <q-card flat bordered class="stat-card stat-card-secondary">
                        <q-card-section>
                            <div class="row items-center no-wrap">
                                <div class="col">
                                    <div class="text-overline text-grey-7">Prescripciones</div>
                                    <div class="text-h3 text-weight-bold text-grey-9 q-mt-sm">0</div>
                                    <div class="text-caption text-positive q-mt-xs">
                                        <q-icon name="trending_up" size="14px" /> +0% este mes
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <q-icon name="medical_services" size="56px" class="stat-icon stat-icon-secondary" />
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <q-card flat bordered class="stat-card stat-card-info">
                        <q-card-section>
                            <div class="row items-center no-wrap">
                                <div class="col">
                                    <div class="text-overline text-grey-7">Cuentas x Pagar</div>
                                    <div class="text-h3 text-weight-bold text-grey-9 q-mt-sm">$0</div>
                                    <div class="text-caption text-grey-6 q-mt-xs">
                                        0 pendientes
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <q-icon name="payments" size="56px" class="stat-icon stat-icon-info" />
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <q-card flat bordered class="stat-card stat-card-warning">
                        <q-card-section>
                            <div class="row items-center no-wrap">
                                <div class="col">
                                    <div class="text-overline text-grey-7">Cuentas x Cobrar</div>
                                    <div class="text-h3 text-weight-bold text-grey-9 q-mt-sm">$0</div>
                                    <div class="text-caption text-grey-6 q-mt-xs">
                                        0 pendientes
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <q-icon name="account_balance_wallet" size="56px" class="stat-icon stat-icon-warning" />
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="row q-col-gutter-lg">
                <div class="col-12 col-lg-8">
                    <q-card flat bordered class="modern-card">
                        <q-card-section class="q-pb-none">
                            <div class="text-h6 text-grey-9 text-weight-medium">Actividad Reciente</div>
                            <div class="text-caption text-grey-6">Últimas acciones en el sistema</div>
                        </q-card-section>
                        <q-separator class="q-my-md" />
                        <q-card-section>
                            <div class="text-center q-pa-xl">
                                <q-icon name="inbox" size="80px" color="grey-4" class="q-mb-md" />
                                <div class="text-h6 text-grey-6">No hay actividad reciente</div>
                                <div class="text-caption text-grey-5 q-mt-sm">Las acciones aparecerán aquí</div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <div class="col-12 col-lg-4">
                    <q-card flat bordered class="modern-card">
                        <q-card-section class="q-pb-none">
                            <div class="text-h6 text-grey-9 text-weight-medium">Accesos Rápidos</div>
                            <div class="text-caption text-grey-6">Funciones principales</div>
                        </q-card-section>
                        <q-separator class="q-my-md" />
                        <q-card-section class="q-pt-none">
                            <q-list>
                                <q-item clickable v-ripple :href="route('pacientes.index')" class="quick-action-item">
                                    <q-item-section avatar>
                                        <q-avatar color="primary" text-color="white" size="40px">
                                            <q-icon name="people" />
                                        </q-avatar>
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label class="text-weight-medium">Gestionar Pacientes</q-item-label>
                                        <q-item-label caption>Ver y administrar pacientes</q-item-label>
                                    </q-item-section>
                                    <q-item-section side>
                                        <q-icon name="chevron_right" color="grey-5" />
                                    </q-item-section>
                                </q-item>

                                <q-separator class="q-my-sm" />

                                <q-item clickable v-ripple :href="route('prescripciones.index')" class="quick-action-item">
                                    <q-item-section avatar>
                                        <q-avatar color="secondary" text-color="white" size="40px">
                                            <q-icon name="medical_services" />
                                        </q-avatar>
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label class="text-weight-medium">Nueva Prescripción</q-item-label>
                                        <q-item-label caption>Crear prescripción médica</q-item-label>
                                    </q-item-section>
                                    <q-item-section side>
                                        <q-icon name="chevron_right" color="grey-5" />
                                    </q-item-section>
                                </q-item>

                                <q-separator class="q-my-sm" />

                                <q-item clickable v-ripple :href="route('configuracion.index')" class="quick-action-item">
                                    <q-item-section avatar>
                                        <q-avatar color="grey-7" text-color="white" size="40px">
                                            <q-icon name="settings" />
                                        </q-avatar>
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label class="text-weight-medium">Configuración</q-item-label>
                                        <q-item-label caption>Ajustes del sistema</q-item-label>
                                    </q-item-section>
                                    <q-item-section side>
                                        <q-icon name="chevron_right" color="grey-5" />
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>

<style scoped>
.modern-dashboard {
    background: #F8F9FA;
}

/* Tarjetas de estadísticas */
.stat-card {
    border-radius: 16px;
    border: 1px solid #E9ECEF;
    transition: all 0.3s ease;
    background: white;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.stat-icon {
    opacity: 0.15;
}

.stat-icon-primary {
    color: #4CAF50;
}

.stat-icon-secondary {
    color: #66BB6A;
}

.stat-icon-info {
    color: #42A5F5;
}

.stat-icon-warning {
    color: #FFA726;
}

/* Tarjetas modernas */
.modern-card {
    border-radius: 16px;
    border: 1px solid #E9ECEF;
    background: white;
}

/* Items de acciones rápidas */
.quick-action-item {
    border-radius: 12px;
    margin: 4px 0;
    transition: all 0.2s ease;
}

.quick-action-item:hover {
    background: #F8F9FA;
}
</style>
