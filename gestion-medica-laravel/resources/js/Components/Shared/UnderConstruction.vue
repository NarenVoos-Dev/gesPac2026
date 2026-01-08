<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Página en Construcción'
    },
    message: {
        type: String,
        default: 'Esta funcionalidad estará disponible próximamente'
    },
    icon: {
        type: String,
        default: 'construction'
    }
});
</script>

<template>
    <div class="flex flex-center q-pa-xl">
        <q-card flat bordered class="text-center" style="max-width: 500px; width: 100%;">
            <q-card-section class="q-pt-xl">
                <q-icon 
                    :name="icon" 
                    size="120px" 
                    color="grey-5"
                    class="q-mb-md"
                />
                
                <div class="text-h5 text-grey-8 q-mb-sm">
                    {{ title }}
                </div>
                
                <div class="text-body2 text-grey-6">
                    {{ message }}
                </div>
            </q-card-section>
            
            
        </q-card>
    </div>
</template>

<style scoped>

</style>
