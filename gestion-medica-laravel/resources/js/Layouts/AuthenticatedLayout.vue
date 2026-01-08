<template>
    <q-layout view="hHh lpR fFf" class="modern-layout">
        <!-- Header / Navbar -->
        <q-header class="bg-white text-grey-8 modern-header">
            <q-toolbar class="q-px-md">
                <q-btn
                    flat
                    dense
                    round
                    icon="menu"
                    aria-label="Menu"
                    @click="toggleLeftDrawer"
                    class="q-mr-sm text-grey-7"
                />

                <q-toolbar-title class="text-weight-medium">
                    <div class="row items-center">
                        <q-icon name="local_hospital" size="28px" color="green-5" class="q-mr-xs" />
                        <span class="text-grey-9 text-weight-medium">GesPac</span>
                    </div>
                </q-toolbar-title>

                <div class="q-gutter-sm row items-center no-wrap">
                    <!-- Notificaciones -->
                    <q-btn flat round dense icon="notifications_none" class="text-grey-7">
                        <q-badge color="negative" floating rounded>3</q-badge>
                        <q-tooltip>Notificaciones</q-tooltip>
                    </q-btn>

                    <!-- Usuario -->
                    <q-btn flat round dense class="q-ml-sm">
                        <q-avatar size="36px" class="shadow-2">
                            <img src="https://cdn.quasar.dev/img/avatar.png">
                        </q-avatar>
                        
                        <q-menu auto-close>
                            <q-list style="min-width: 220px" class="rounded-borders">
                                <q-item class="q-pa-md">
                                    <q-item-section avatar>
                                        <q-avatar size="48px">
                                            <img src="https://cdn.quasar.dev/img/avatar.png">
                                        </q-avatar>
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label class="text-weight-medium">{{ user?.name || 'Usuario' }}</q-item-label>
                                        <q-item-label caption class="text-grey-6">{{ user?.email }}</q-item-label>
                                    </q-item-section>
                                </q-item>

                                <q-separator class="q-my-none" />

                                <q-item clickable v-ripple class="rounded-item">
                                    <q-item-section avatar>
                                        <q-icon name="person_outline" color="grey-7" />
                                    </q-item-section>
                                    <q-item-section>Mi Perfil</q-item-section>
                                </q-item>

                                <q-item clickable v-ripple class="rounded-item">
                                    <q-item-section avatar>
                                        <q-icon name="settings" color="grey-7" />
                                    </q-item-section>
                                    <q-item-section>Configuración</q-item-section>
                                </q-item>

                                <q-separator class="q-my-none" />

                                <q-item clickable v-ripple class="rounded-item">
                                    <q-item-section avatar>
                                        <q-icon name="logout" color="negative" />
                                    </q-item-section>
                                    <q-item-section>
                                        <Link :href="route('logout')" method="post" as="button" class="text-negative text-weight-medium">
                                            Cerrar Sesión
                                        </Link>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </q-menu>
                    </q-btn>
                </div>
            </q-toolbar>
        </q-header>

        <!-- Sidebar / Drawer -->
        <q-drawer
            v-model="leftDrawerOpen"
            :width="260"
            :breakpoint="0"
            overlay
            behavior="mobile"
            class="modern-drawer"
        >
            <div class="drawer-content">
                <!-- Header del Sidebar con Logo y Botón Cerrar -->
                <div class="sidebar-header">
                    <div class="row items-center justify-between q-pa-md">
                        <div class="row items-center">
                            <q-icon name="local_hospital" size="32px" color="green-5" class="q-mr-sm" />
                            <div>
                                <div class="text-h6 text-grey-9 text-weight-bold">GesPac</div>
                                <div class="text-caption text-grey-6">Gestion de Pacientes</div>
                            </div>
                        </div>
                        <q-btn 
                            flat 
                            round 
                            dense 
                            icon="close" 
                            @click="toggleLeftDrawer"
                            class="text-grey-7"
                        >
                            <q-tooltip>Cerrar menú</q-tooltip>
                        </q-btn>
                    </div>
                    <q-separator />
                </div>

                <!-- Menú de navegación -->
                <q-list class="q-pa-md q-gutter-y-xs">

                    <template v-for="(item, index) in menuItems" :key="index">
                        <q-item
                            clickable
                            v-ripple
                            :active="isActiveRoute(item.route)"
                            :href="route(item.route)"
                            class="menu-item"
                            :class="{ 'menu-item-active': isActiveRoute(item.route) }"
                        >
                            <q-item-section avatar>
                                <q-icon :name="item.icon" size="22px" />
                            </q-item-section>

                            <q-item-section>
                                <q-item-label class="text-weight-medium">{{ item.title }}</q-item-label>
                            </q-item-section>
                        </q-item>

                        <q-separator 
                            v-if="item.separator" 
                            class="q-my-md q-mx-md" 
                        />
                    </template>
                </q-list>

                <!-- Footer -->
                <div class="absolute-bottom q-pa-md text-center">
                    <div class="text-caption text-grey-5">
                        Versión 1.0.0 · {{ new Date().getFullYear() }}
                    </div>
                </div>
            </div>
        </q-drawer>

        <!-- Contenido principal -->
        <q-page-container class="modern-page-container">
            <slot />
        </q-page-container>
    </q-layout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const leftDrawerOpen = ref(false);

// Obtener usuario autenticado
const user = computed(() => page.props.auth?.user);

// Menú de navegación
const menuItems = [
    {
        title: 'Dashboard',
        icon: 'dashboard',
        route: 'dashboard',
        separator: true
    },
    {
        title: 'Pacientes',
        icon: 'people',
        route: 'pacientes.index'
    },
    {
        title: 'Prescripciones',
        icon: 'medical_services',
        route: 'prescripciones.index'
    },
    {
        title: 'Cuentas por Pagar',
        icon: 'payments',
        route: 'cuentas-pagar.index'
    },
    {
        title: 'Cuentas por Cobrar',
        icon: 'account_balance_wallet',
        route: 'cuentas-cobrar.index',
        separator: true
    },
    {
        title: 'Configuración',
        icon: 'settings',
        route: 'configuracion.index'
    },
    {
        title: 'Roles y Permisos',
        icon: 'admin_panel_settings',
        route: 'roles.index'
    }
];

// Verificar si la ruta está activa
const isActiveRoute = (routeName) => {
    return route().current(routeName);
};

// Toggle drawer
const toggleLeftDrawer = () => {
    leftDrawerOpen.value = !leftDrawerOpen.value;
};
</script>

<style scoped>
/* Layout moderno */
.modern-layout {
    background: #F8F9FA;
}

/* Header moderno - Sin bordes */
.modern-header {
    box-shadow: none !important;
    border: none !important;
    border-bottom: none !important;
}

.modern-header :deep(.q-toolbar) {
    border-bottom: none !important;
}

/* Drawer moderno - Blanco con borde derecho */
.modern-drawer {
    background: #FFFFFF !important;
    border-right: 1px solid #E0E0E0 !important;
    box-shadow: none !important;
}

.drawer-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Header del Sidebar */
.sidebar-header {
    background: white;
}

.sidebar-header .q-btn {
    transition: all 0.2s ease;
}

.sidebar-header .q-btn:hover {
    background: #F5F5F5;
    transform: rotate(90deg);
}

/* Items del menú - Verde claro por defecto */
.menu-item {
    border-radius: 12px;
    margin: 4px 0;
    padding: 12px 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #4CAF50 !important;
}

.menu-item:hover {
    background: #F1F8F4 !important;
    transform: translateX(4px);
}

/* Item activo - Fondo verde con texto blanco */
.menu-item-active {
    background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%) !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.25);
    transform: translateX(4px);
}

.menu-item-active :deep(.q-icon),
.menu-item-active :deep(.q-item__label) {
    color: #FFFFFF !important;
}

/* Iconos del menú */
.menu-item :deep(.q-icon) {
    color: #4CAF50;
}

.menu-item-active :deep(.q-icon) {
    color: #FFFFFF !important;
}

/* Container de páginas */
.modern-page-container {
    background: #F8F9FA;
}

/* Items redondeados en menú de usuario */
.rounded-item {
    border-radius: 8px;
    margin: 2px 8px;
}

.rounded-item:hover {
    background: #F8F9FA;
}

/* Mejoras visuales */
:deep(.q-toolbar) {
    min-height: 64px;
}

:deep(.q-drawer) {
    border-radius: 0;
}

/* Separadores más sutiles */
:deep(.q-separator) {
    background: #E9ECEF;
}

/* Sombras sutiles para avatares */
.shadow-2 {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
