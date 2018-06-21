<template>
  	<div class="modal-body">
    	<p>One fine body&hellip;</p>
  	</div>
</template>

<script>
export default{
	methods:{
		close(){
			this.$emit('cm');
		}
	}
}
</script>