<template>
    <section class="testplace container-fluid bg-white">

        <h4>{{ data }}</h4>

        <div class="form-group">
            <input-ui v-model="data" placeholder="Комментарий" type="email" tooltip="Hello world!!!!"/>
        </div>

    </section>
</template>

<script>
export default {
    name: "TestPlace",
    data(){
        return {
            data: 'test'
        }
    }
}
</script>
