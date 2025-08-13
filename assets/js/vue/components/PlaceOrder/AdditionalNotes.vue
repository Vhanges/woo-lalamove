<template>
  <div class="additional-notes-wrapper">
    <div class="notes-input-group">
      <label for="additional_notes" class="notes-label">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="notes-icon">
          <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" stroke="currentColor" stroke-width="2"/>
          <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/>
          <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2"/>
          <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2"/>
          <polyline points="10,9 9,10 7,8" stroke="currentColor" stroke-width="2"/>
        </svg>
        Special Instructions
      </label>
      <div class="textarea-container">
        <textarea
          name="additional_notes"
          id="additional_notes"
          v-model="notes"
          class="notes-textarea"
          placeholder="Add any special delivery instructions, package handling notes, or important information for the driver..."
          rows="4"
        ></textarea>
        <div class="character-counter">
          <span class="counter-text">{{ notes.length }}/500</span>
        </div>
      </div>
      <div class="notes-suggestions">
        <p class="suggestions-title">Common instructions:</p>
        <div class="suggestion-tags">
          <button 
            v-for="suggestion in suggestions" 
            :key="suggestion"
            type="button"
            class="suggestion-tag"
            @click="addSuggestion(suggestion)"
          >
            {{ suggestion }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {storeToRefs} from 'pinia'
import { useLalamoveStore } from '../../store/lalamoveStore';
import { ref, watch } from "vue";

let debounceTimer;

const lalamove = useLalamoveStore();
const {additionalNotes} = storeToRefs(lalamove);
const notes = ref('');

const suggestions = [
  "Handle with care",
  "Fragile items inside",
  "Call upon arrival",
  "Leave at front door",
  "Ring doorbell",
  "No signature required"
];

// Add suggestion to notes
const addSuggestion = (suggestion) => {
  if (!notes.value.includes(suggestion)) {
    notes.value = notes.value ? `${notes.value}\n• ${suggestion}` : `• ${suggestion}`;
  }
};

// push back to store on user edit, debounced
watch(notes, (val) => {
  // Limit character count
  if (val.length > 500) {
    notes.value = val.substring(0, 500);
    return;
  }
  
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    additionalNotes.value = val;
    console.log("Debounced update:", val);
  }, 2000); // Reduced debounce time
});

// keep notes in sync if store ever changes
watch(
  additionalNotes,
  (val) => {
    notes.value = val;
    console.log("Store pushed update:", val);
  },
  { immediate: true }
);
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;
@use "@/css/scss/_mixins.scss" as *;

.additional-notes-wrapper {
  width: 100%;
}

.notes-input-group {
  width: 100%;
}

.notes-label {
  @include small-text;
  font-weight: $font-weight-medium;
  color: $txt-secondary;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.notes-icon {
  color: $bg-primary;
}

.textarea-container {
  position: relative;
  margin-bottom: 1rem;
}

.notes-textarea {
  @include form-input;
  width: 100%;
  min-height: 100px;
  resize: vertical;
  font-family: $font-primary;
  line-height: 1.5;
  
  &::placeholder {
    color: rgba($txt-primary, 0.5);
    line-height: 1.5;
  }
  
  &:focus {
    border-color: $bg-primary;
    box-shadow: 0 0 0 3px rgba($bg-primary, 0.1);
  }
  
  @include respond-above('md') {
    min-height: 120px;
  }
}

.character-counter {
  position: absolute;
  bottom: 0.5rem;
  right: 0.75rem;
  pointer-events: none;
}

.counter-text {
  @include small-text;
  color: rgba($txt-primary, 0.5);
  background: rgba($bg-high-light, 0.9);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: $font-size-xs;
}

.notes-suggestions {
  margin-top: 1rem;
}

.suggestions-title {
  @include small-text;
  font-weight: $font-weight-medium;
  color: $txt-secondary;
  margin: 0 0 0.5rem 0;
}

.suggestion-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.suggestion-tag {
  @include btn-base;
  background: rgba($bg-primary, 0.1);
  color: $bg-primary;
  border: 1px solid rgba($bg-primary, 0.2);
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: $font-size-xs;
  font-weight: $font-weight-regular;
  
  &:hover {
    background: rgba($bg-primary, 0.15);
    border-color: rgba($bg-primary, 0.3);
    transform: translateY(-1px);
  }
  
  &:active {
    transform: translateY(0);
  }
}

// Mobile adjustments
@media (max-width: 767px) {
  .notes-textarea {
    min-height: 80px;
    padding: 0.75rem;
    font-size: $font-size-sm;
  }
  
  .character-counter {
    bottom: 0.25rem;
    right: 0.5rem;
  }
  
  .suggestion-tag {
    padding: 0.25rem 0.5rem;
    font-size: $font-size-xs;
  }
}

// High contrast mode
@media (prefers-contrast: high) {
  .notes-textarea {
    border: 2px solid $border-color;
    
    &:focus {
      border: 2px solid $bg-primary;
    }
  }
  
  .suggestion-tag {
    border: 2px solid $bg-primary;
  }
}

// Reduced motion
@media (prefers-reduced-motion: reduce) {
  .suggestion-tag {
    &:hover {
      transform: none;
    }
    
    &:active {
      transform: none;
    }
  }
}
</style>
