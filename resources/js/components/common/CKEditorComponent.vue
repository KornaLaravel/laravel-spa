<template>
  <Ckeditor v-model="editorData" :editor="ClassicEditor" :config="editorConfig" />
</template>

<script setup>
import { ref, watch } from 'vue';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import { BlockQuote, Bold, ClassicEditor, Essentials, Heading, Italic, Link, List, Paragraph } from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

const props = defineProps({
  modelValue: String,
});
const editorData = ref(props.modelValue || '');
const emit = defineEmits(['update:modelValue']);

// CKEditor 5 requires a license key from v44 onwards. "GPL" selects the
// open source licence when no commercial key is configured.
const licenseKey = CK_EDITOR_KEY || 'GPL';

const editorConfig = ref({
  licenseKey,
  plugins: [BlockQuote, Bold, Essentials, Heading, Italic, Link, List, Paragraph],
  toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
  heading: {
    options: [
      { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
      {
        model: 'heading1',
        view: 'h1',
        title: 'Heading 1',
        class: 'ck-heading_heading1',
      },
      {
        model: 'heading2',
        view: 'h2',
        title: 'Heading 2',
        class: 'ck-heading_heading2',
      },
    ],
  },
});

watch(editorData, () => {
  emit('update:modelValue', editorData.value);
});

watch(
  () => props.modelValue,
  (value) => {
    if (value !== editorData.value) {
      editorData.value = value || '';
    }
  },
);
</script>

<style>
.ck-editor__editable {
  min-height: 260px;
}
</style>
