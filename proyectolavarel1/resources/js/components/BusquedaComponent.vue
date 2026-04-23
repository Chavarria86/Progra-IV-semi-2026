<template>
  <div class="input-group mb-4">
    <span class="input-group-text bg-light">🔍</span>
    <input 
      type="text" 
      :placeholder="placeholder" 
      v-model="query" 
      @keyup="onSearch"
      class="form-control"
    />
    <button type="button" @click="onSearch" class="btn btn-secondary px-4">Buscar</button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Buscar...'
  }
});

const emit = defineEmits(['search']);

const query = ref('');
let timeout = null;

const onSearch = () => {
  if (timeout) clearTimeout(timeout);
  timeout = setTimeout(() => {
    emit('search', query.value);
  }, 300); // debounce
};
</script>
