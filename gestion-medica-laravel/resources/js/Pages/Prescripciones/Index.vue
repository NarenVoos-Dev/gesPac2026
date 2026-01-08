<template>
    <Head title="Prescripciones" />

    <AuthenticatedLayout>
        <q-page>
            <UnderConstruction 
                title="Módulo de Prescripciones"
                message="El módulo de prescripciones médicas estará disponible próximamente"
                icon="medical_services"
            />
        </q-page>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UnderConstruction from '@/Components/Shared/UnderConstruction.vue';
import { Head } from '@inertiajs/vue3';
</script>