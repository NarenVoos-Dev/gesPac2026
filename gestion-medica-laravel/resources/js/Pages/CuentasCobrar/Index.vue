<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UnderConstruction from '@/Components/Shared/UnderConstruction.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Cuentas por Cobrar" />

    <AuthenticatedLayout>
        <q-page>
            <UnderConstruction 
                title="Cuentas por Cobrar"
                message="El módulo de cuentas por cobrar estará disponible próximamente"
                icon="account_balance_wallet"
            />
        </q-page>
    </AuthenticatedLayout>
</template>
