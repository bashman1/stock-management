<script setup>
import { ref, onMounted } from 'vue';
</script>

<template>
    <div class="backdrop">
        <!-- <div class="lds-facebook">
            <div></div>
            <div></div>
            <div></div>
        </div> -->
        <span class="loader"></span>
    </div>
</template>

<style>
/* Backdrop to cover the entire screen */
.backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.3); /* Black with 60% opacity */
  display: flex;
  justify-content: center; /* Horizontally center */
  align-items: center; /* Vertically center */
  z-index: 9999; /* Ensure it appears on top of everything */
}

/* Spinner styles */
.lds-facebook,
.lds-facebook div {
  box-sizing: border-box;
  color: #77BA1B; /* White spinner color */
}

.lds-facebook {
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}

.lds-facebook div {
  display: inline-block;
  position: absolute;
  left: 8px;
  width: 16px;
  background: currentColor;
  animation: lds-facebook 0.4s cubic-bezier(0, 0.5, 0.5, 1) infinite;
}

.lds-facebook div:nth-child(1) {
  left: 8px;
  animation-delay: -0.24s;
}

.lds-facebook div:nth-child(2) {
  left: 32px;
  animation-delay: -0.12s;
}

.lds-facebook div:nth-child(3) {
  left: 56px;
  animation-delay: 0s;
}

@keyframes lds-facebook {
  0% {
    top: 8px;
    height: 64px;
  }
  50%, 100% {
    top: 24px;
    height: 32px;
  }
}



/**


**/


.loader {
    position: relative;
    width: 85px;
    height: 50px;
    background-repeat: no-repeat;
    background-image: linear-gradient(#77BA1B 50px, transparent 0),
                      linear-gradient(#77BA1B 50px, transparent 0),
                      linear-gradient(#77BA1B 50px, transparent 0),
                      linear-gradient(#77BA1B 50px, transparent 0),
                      linear-gradient(#77BA1B 50px, transparent 0),
                      linear-gradient(#77BA1B 50px, transparent 0);
    background-position: 0px center, 15px center, 30px center, 45px center, 60px center, 75px center, 90px center;
    animation: rikSpikeRoll 0.65s linear infinite alternate;
  }
@keyframes rikSpikeRoll {
  0% { background-size: 10px 3px;}
  16% { background-size: 10px 50px, 10px 3px, 10px 3px, 10px 3px, 10px 3px, 10px 3px}
  33% { background-size: 10px 30px, 10px 50px, 10px 3px, 10px 3px, 10px 3px, 10px 3px}
  50% { background-size: 10px 10px, 10px 30px, 10px 50px, 10px 3px, 10px 3px, 10px 3px}
  66% { background-size: 10px 3px, 10px 10px, 10px 30px, 10px 50px, 10px 3px, 10px 3px}
  83% { background-size: 10px 3px, 10px 3px,  10px 10px, 10px 30px, 10px 50px, 10px 3px}
  100% { background-size: 10px 3px, 10px 3px, 10px 3px,  10px 10px, 10px 30px, 10px 50px}
}
    /**  **/


</style>

