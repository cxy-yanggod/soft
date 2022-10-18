<template>
  <div id="app">
    <div class="content" v-if="article">
      <h3 class="detitle">{{article.title}}</h3>
      <div class="user disbox">
        <img :src="user_info.avatar">
        <div class="info disflex">
          <h3>{{article.author}}</h3>
          <p>{{article.created_at}}</p>
        </div>
        <div style="position: relative;left: 40px;top: 10px;">
          <a-button @click="download_app">下载APP</a-button>
        </div>
      </div>
      <br>
      <div class="html" v-html="article.html"></div>
      <div data-clipboard-text="test" class="app-downss btn flex">
        <img :src="app_info.logo">
        <div class="info">
          <h3>{{app_info.app_name}}</h3> <p>下载app查看更多资源</p>
        </div>
        <a @click="download_app">立即下载</a>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import CryptoJS from '../../CryptoJS';
const AES_KEY = "aksgdigasifgiacs";
const AES_IV = "asdasdhoashoasas";

function aes_encrypt(plainText) {
  var encrypted = CryptoJS.AES.encrypt(plainText, CryptoJS.enc.Utf8.parse(AES_KEY), {iv:  CryptoJS.enc.Utf8.parse(AES_IV)});
  return CryptoJS.enc.Base64.stringify(encrypted.ciphertext);
}

function aes_decrypt(ciphertext) {
  var decrypted = CryptoJS.AES.decrypt(ciphertext, CryptoJS.enc.Utf8.parse(AES_KEY), {iv: CryptoJS.enc.Utf8.parse(AES_IV)});
  return decrypted.toString(CryptoJS.enc.Utf8);
}


export default {
  data() {
    return {
      article:{},
      app_info:{},
      user_info:{}
    };
  },
  mounted(){
    this.details()
  },
  methods:{
    details(){
      axios.get('/api/v2/article/share_article?id='+this.$route.params.id).then((response)=>{
        this.article = JSON.parse(aes_decrypt(response.data)).result
        this.get_app_info()
        this.get_user_info()
      })
    },
    get_app_info(){
      axios.get('/api/v2/app_info?app_user_id='+this.article.user_id).then((response)=>{
        this.app_info = JSON.parse(aes_decrypt(response.data)).result
      })
    },
    download_app(){
      window.open(this.app_info.app_download)
    },
    get_user_info(){
      axios.get('/api/v2/app_info/user?app_user_id='+this.article.user_id).then((response)=>{
        this.user_info = JSON.parse(aes_decrypt(response.data)).result
      })
    }
  }
};
</script>

<style lang="scss">
.html{
  clear: both;
  word-break: break-word;
  word-wrap: break-word;
  white-space: -moz-pre-wrap;
  white-space: -hp-pre-wrap;
  white-space: -o-pre-wrap;
  white-space: -pre-wrap;
  white-space: pre;
  white-space: pre-wrap;
  white-space: pre-line;
  img{
    width: 100%;
  }
}

  #app {
    max-width: 700px;
    margin: 0px auto;
  }

  .content{
    padding: 20px;
    padding-top: 10px;
    padding-bottom: 10px;
    background: #FFF;
    .detitle {
      font-size: 20px;
      font-weight: blod;
    }
    .user {
      padding: 10px 20px;
      border-radius: 10px;
      background: #fbfbfb;
        img {
        display: block;
        width: 40px;
        height: 40px;
        border-radius: 50%;
      }
      .info {
        padding-left: 20px;
        p {
          color: #666d7f;
          font-size: 14px;
          margin: 0px;
          padding: 0px;
        }
        h3 {
          font-size: 14px;
          font-weight: normal;
          color: #000;
          margin: 0px;
        }
      }

    }
    .disbox {
      display: -webkit-box!important;
      display: -moz-box;
      box-sizing: border-box;
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
    }
  }
  .app-downss {
    background: rgba(0,0,0,0.5);
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    flex-direction: row;
    align-items: center;
    display: flex;
    padding: 10px 15px;
    height: 100px;
    img {
      width: 50px;
      height: 50px;
      border-radius: 10px;
    }
    .info {
      margin-left: 15px;
      padding-top: 5px;
      width: 0;
      flex: 1;
      h3 {
        color: #FFF;
        font-size: 15px;
        line-height: 10px;
      }
      p {
        color: #FFF;
        font-size: 15px;
        margin-top: 10px;
        line-height: 10px;
        opacity: 0.7;
      }
    }
    a {
      background: #fe9610;
      color: #FFF;
      font-size: 15px;
      height: 40px;
      line-height: 40px;
      padding: 0 10px;
      border-radius: 10px;
    }
  }
</style>
