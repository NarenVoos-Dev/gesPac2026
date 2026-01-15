<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="text-h6 text-grey-8">Gestión de Entregas</div>
        </template>

        <div class="q-pa-md">
            <!-- Breadcrumbs -->
            <div class="row items-center q-mb-md">
                <q-breadcrumbs class="text-grey" active-color="primary">
                    <template v-slot:separator>
                        <q-icon size="1.2em" name="chevron_right" color="primary" />
                    </template>
                    <q-breadcrumbs-el label="Inicio" icon="home" to="/dashboard" />
                    <q-breadcrumbs-el label="Entregas" icon="local_shipping" />
                </q-breadcrumbs>
            </div>

            <EntregasComponent />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntregasComponent from './Components/EntregasComponent.vue';
import { Head } from '@inertiajs/vue3';
</script>
