<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UnderConstruction from '@/Components/Shared/UnderConstruction.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Roles y Permisos" />

    <AuthenticatedLayout>
        <q-page>
            <UnderConstruction 
                title="Roles y Permisos"
                message="El módulo de gestión de roles y permisos estará disponible próximamente"
                icon="admin_panel_settings"
            />
        </q-page>
    </AuthenticatedLayout>
</template>
