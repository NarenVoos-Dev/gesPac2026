<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión" />

    <div class="login-container">
        <!-- FONDO CON GRADIENTE -->
        <div class="background-gradient"></div>

        <!-- CARD DE LOGIN -->
        <q-card class="login-card">
            <!-- LOGO Y TÍTULO -->
            <q-card-section class="text-center q-pt-xl q-pb-md">
                <div class="logo-container">
                    <q-icon name="local_hospital" size="64px" color="primary" />
                </div>
                <div class="text-h4 text-weight-bold text-grey-9 q-mt-md">GesPac</div>
                <div class="text-subtitle1 text-grey-6">Gestión de Pacientes</div>
            </q-card-section>

            <!-- MENSAJE DE ESTADO -->
            <q-card-section v-if="status" class="q-pt-none">
                <q-banner class="bg-positive text-white" rounded>
                    <template v-slot:avatar>
                        <q-icon name="check_circle" />
                    </template>
                    {{ status }}
                </q-banner>
            </q-card-section>

            <!-- FORMULARIO -->
            <q-card-section class="q-px-xl q-pb-xl">
                <q-form @submit="submit" class="q-gutter-md">
                    <!-- EMAIL -->
                    <q-input
                        v-model="form.email"
                        type="email"
                        label="Correo electrónico"
                        outlined
                        dense
                        :error="!!form.errors.email"
                        :error-message="form.errors.email"
                        autofocus
                        autocomplete="username"
                    >
                        <template v-slot:prepend>
                            <q-icon name="email" color="primary" />
                        </template>
                    </q-input>

                    <!-- PASSWORD -->
                    <q-input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        label="Contraseña"
                        outlined
                        dense
                        :error="!!form.errors.password"
                        :error-message="form.errors.password"
                        autocomplete="current-password"
                    >
                        <template v-slot:prepend>
                            <q-icon name="lock" color="primary" />
                        </template>
                        <template v-slot:append>
                            <q-icon
                                :name="showPassword ? 'visibility_off' : 'visibility'"
                                class="cursor-pointer"
                                @click="showPassword = !showPassword"
                            />
                        </template>
                    </q-input>

                    <!-- REMEMBER ME -->
                    <div class="q-mt-md">
                        <q-checkbox
                            v-model="form.remember"
                            label="Recordarme"
                            color="primary"
                        />
                    </div>

                    <!-- BOTÓN DE LOGIN -->
                    <q-btn
                        type="submit"
                        label="Iniciar Sesión"
                        color="primary"
                        unelevated
                        size="lg"
                        class="full-width q-mt-lg"
                        :loading="form.processing"
                        :disable="form.processing"
                    >
                        <template v-slot:loading>
                            <q-spinner-dots />
                        </template>
                    </q-btn>
                </q-form>
            </q-card-section>

            <!-- FOOTER -->
            <q-card-section class="text-center q-pt-none q-pb-lg">
                <div class="text-caption text-grey-6">
                    © {{ new Date().getFullYear() }} GesPac. Todos los derechos reservados.
                </div>
            </q-card-section>
        </q-card>
    </div>
</template>

<style scoped>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: #F8F9FA;
}

/* Fondo con gradiente animado */
.background-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 50%, #81C784 100%);
    opacity: 0.1;
    z-index: 0;
}

.background-gradient::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(76, 175, 80, 0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Card de login */
.login-card {
    width: 100%;
    max-width: 450px;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
    background: white;
}

/* Logo container */
.logo-container {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
}

/* Inputs personalizados */
:deep(.q-field__label) {
    font-size: 14px;
    font-weight: 500;
}

:deep(.q-field__control) {
    font-size: 14px;
}

/* Quitar outline azul */
:deep(.q-field--focused .q-field__control) {
    box-shadow: none !important;
    outline: none !important;
}

:deep(.q-field--focused .q-field__control:after) {
    display: none !important;
}

:deep(.q-field--outlined.q-field--focused .q-field__control:before) {
    border-color: #4CAF50 !important;
    border-width: 2px !important;
}

:deep(input),
:deep(textarea) {
    outline: none !important;
    box-shadow: none !important;
}

:deep(input:focus),
:deep(textarea:focus) {
    outline: none !important;
    box-shadow: none !important;
}

/* Botón de login */
:deep(.q-btn) {
    border-radius: 8px;
    font-weight: 600;
    text-transform: none;
    letter-spacing: 0.5px;
}

/* Responsive */
@media (max-width: 600px) {
    .login-card {
        max-width: 90%;
        margin: 20px;
    }

    :deep(.q-card__section) {
        padding-left: 24px !important;
        padding-right: 24px !important;
    }
}
</style>
