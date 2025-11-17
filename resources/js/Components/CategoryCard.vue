<template>
  <div class="category-card d-flex align-items-center">
    <!-- صورة القسم مع حجم محدود -->
    <img 
      :src="image" 
      :alt="name"
      class="category-image me-2"
      @error="handleImageError"
    />
    <div class="category-info">
      <h6 class="mb-0">{{ name }}</h6>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CategoryCard',
  props: {
    image: {
      type: String,
      required: true
    },
    name: {
      type: String,
      required: true
    }
  },
  methods: {
    handleImageError(event) {
      // استخدام الصورة الافتراضية عند فشل تحميل الصورة
      event.target.src = '/default-placeholder.png'
    }
  }
}
</script>

<style scoped>
.category-card {
  max-width: 200px;
}

.category-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e3e6f0;
  flex-shrink: 0;
}

.category-info {
  flex-grow: 1;
  min-width: 0;
}

.category-info h6 {
  font-size: 0.875rem;
  color: #5a5c69;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
