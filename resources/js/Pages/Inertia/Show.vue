<script setup>
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'

defineProps({
    id:String,
    blog:Object
});

const deleteConfirm=(id)=>{
//    console.log(id)
    router.delete(`/inertia/${id}`,{
        onBefore:()=>confirm('本当に削除しますか？')
    })
};
</script>

<template>
{{ id }}<br>
{{ blog.title }}<br>
<button @click="deleteConfirm(blog.id)">削除</button>

</template>
