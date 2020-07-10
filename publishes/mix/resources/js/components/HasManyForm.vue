<template>
    <div>
        <h1>Incomplete</h1>
        <slot></slot>
    </div>    
</template>
<script charset="utf-8">
export default {
    data() {
        return {
            models: null
        }
    },

    computed: {
        // add row for model
        addRace() {
            let length = this.models.length;
            this.$set(this.models, length, {
                id: null,
                is_active: null,
                workgroup_id: null,
                name: null
            });
        },

        // delete row for model
        deleteRace(index) {
            if (this.models[index].id) {
                let tempPayload = this.models[index];

                tempPayload._destroy = true;
                this.$set(this.models, index, tempPayload);
            } else {
                this.models.splice(index, 1);
            }
        },
    }
}   
</script>
