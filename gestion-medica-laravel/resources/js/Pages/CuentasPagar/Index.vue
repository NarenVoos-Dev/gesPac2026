<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UnderConstruction from '@/Components/Shared/UnderConstruction.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Cuentas por Pagar" />

    <AuthenticatedLayout>
        <q-page>
            <UnderConstruction 
                title="Cuentas por Pagar"
                message="El módulo de cuentas por pagar estará disponible próximamente"
                icon="payments"
            />
        </q-page>
    </AuthenticatedLayout>
</template>
