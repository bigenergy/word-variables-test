<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-5">
                <div class="card">
                    <div class="card-header">Загрузка WORD файла с переменными</div>

                    <div class="card-body">
                        <form @submit="formSubmit" enctype="multipart/form-data">
                            <input type="file" class="form-control" v-on:change="onChange">

                            <div class="mt-3" v-if="!showEditForm">
                                <button class="btn btn-success btn-block">Загрузить</button>
                            </div>
                            <div class="mt-3" v-else>
                                <b>Документ загружен, укажите переменные в нижней карточке</b>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8 mt-5 mb-5" v-if="showEditForm">
                <div class="card">
                    <div class="card-header">Внесение переменных в файл</div>
                    <div class="card-body">
                        <form @submit.prevent="formSubmitEdited">
                            <div v-for="input in editFormData">
                                <div class="mb-3">
                                    <label for="exampleInput" class="form-label">Переменная {{input}}</label>
                                    <input type="text" class="form-control" id="exampleInput" v-model="inputFormData.inputs[input]">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-success btn-block">Внести переменные и скачать PDF файл</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "MainPageComponent",
        data() {
            return {
                file: '',
                showEditForm: false,
                editFormData: [],
                inputFormData: {
                    inputs: {}
                }
            }
        },
        methods: {
            onChange(e) {
                this.file = e.target.files[0];
            },
            formSubmit(e) {
                e.preventDefault();
                let existingObj = this;
                const config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                }
                let data = new FormData();
                data.append('file', this.file);
                axios.post('/file/upload', data, config)
                    .then(function (res) {
                        existingObj.success = res.data.success;
                        existingObj.editFormData = res.data;
                        existingObj.showEditForm = true;
                    })
                    .catch(function (err) {
                        alert("Ошибка загрузки файла")
                        existingObj.output = err;
                    });

            },

            formSubmitEdited() {
                const config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                }

                let formData = new FormData();
                formData.append('file', this.file);

                const reg = [this.inputFormData.inputs]
                reg.forEach(obj => {
                    Object.entries(obj).forEach(item => {
                        formData.append(item[0], item[1])
                    })
                })

                axios.post('/file/convert', formData, config).then(response => {
                    if (response.status === 200) {
                        window.open(response.data.url);
                    }
                }).catch(error => {
                    alert("Операция завершилась с ошибкой")
                });


                console.log(this.inputFormData.inputs)


            }



        }

    }
</script>
