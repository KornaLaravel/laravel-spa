<template>
  <UmoEditor v-bind="options" />
</template>

<script setup>
import { ref } from 'vue';
import { UmoEditor } from '@umoteam/editor';
import '@umoteam/editor/style';

const options = ref({
  theme: 'light',
});
</script>
