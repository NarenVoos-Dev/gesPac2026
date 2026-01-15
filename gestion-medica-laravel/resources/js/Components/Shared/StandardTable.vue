<template>
    <div class="standard-table-container">
        <!-- HEADER CON BOTÓN NUEVO Y SLOT PARA FILTROS -->
        <div class="row items-center justify-between q-mb-sm">
            <div>
                <div class="text-h6 text-grey-9">{{ title }}</div>
                <div class="text-caption text-grey-6">{{ subtitle }}</div>
            </div>
            <div class="row items-center q-gutter-sm">
                <!-- Slot para filtros personalizados -->
                <slot name="filters"></slot>

                <q-input
                    outlined
                    dense
                    debounce="300"
                    v-model="internalFilter"
                    placeholder="Buscar"
                >
                    <template v-slot:append>
                        <q-icon name="search" />
                    </template>
                </q-input>
                
                <!-- Botón Nuevo -->
                <q-btn
                    v-if="showNewButton"
                    color="primary"
                    icon="add"
                    :label="newButtonLabel"
                    @click="$emit('new')"
                    unelevated
                />
            </div>
        </div>

            <!-- TABLA -->
        <q-table
            v-bind="$attrs"
            :rows="rows"
            :columns="columns"
            :row-key="rowKey"
            :loading="loading"
            flat
            bordered
            dense
            :rows-per-page-options="[10, 25, 50]"
            class="modern-table"
        >
            <!-- Slots personalizados (excluyendo top-right) -->
            <template v-for="(_, slot) in $slots" v-slot:[slot]="scope">
                <slot v-if="slot !== 'top-right'" :name="slot" v-bind="scope" />
            </template>
        </q-table>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    rows: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    rowKey: {
        type: String,
        default: 'id',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    showNewButton: {
        type: Boolean,
        default: true,
    },
    newButtonLabel: {
        type: String,
        default: 'Nuevo',
    },
    filter: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['new', 'update:filter']);

const internalFilter = ref(props.filter);

watch(() => props.filter, (val) => {
    if (val !== internalFilter.value) {
        internalFilter.value = val;
    }
});

watch(internalFilter, (val) => {
    emit('update:filter', val);
});
</script>

<style scoped>


.modern-table {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Tabla compacta */
:deep(.q-table) {
    font-size: 13px;
}

:deep(.q-table th) {
    font-weight: 600;
    background-color: #f5f5f5;
}

:deep(.q-table tbody td) {
    padding: 8px 12px;
}

:deep(.q-table thead tr),
:deep(.q-table tbody tr) {
    height: 40px;
}
</style>
