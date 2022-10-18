<template>
  <a-result status="404" title="404" sub-title="页面不存在">
    <template #extra>
      <a-button type="primary" @click="home">
        回到主页
      </a-button>
    </template>
  </a-result>
</template>
<script>
export default {
  data() {
    return {};
  },
  methods:{
    home(){
      window.location.href = '/'
    }
  }
};
</script>