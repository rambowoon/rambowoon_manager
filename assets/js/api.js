const Api = {
    async fetch(action, options = {}) {
        const url = `api.php?action=${action}`;
        const res = await fetch(url, options);
        return await res.json();
    },

    async getCategories(strict = false) {
        return this.fetch(`listCategories${strict ? '&strict=true' : ''}`);
    },

    async getProjects(category) {
        return this.fetch(`listProjects&category=${category}`);
    },

    async saveConfig(name, config) {
        return this.fetch('saveConfig', {
            method: 'POST',
            body: JSON.stringify({ name, config })
        });
    },

    async deploy(name) {
        return this.fetch('deploy', {
            method: 'POST',
            body: JSON.stringify({ name })
        });
    },

    async getProjectConfig(name) {
        return this.fetch(`getProjectConfig&name=${name}`);
    },

    async getProjectSchemaList(name) {
        return this.fetch(`getProjectSchemaList&name=${name}`);
    },

    async loadModuleSchema(name, file) {
        return this.fetch(`loadModuleSchema&name=${name}&file=${file}`);
    },

    async getTypeImageSize(name, type) {
        return this.fetch(
            `getTypeImageSize&name=${encodeURIComponent(name)}&type=${encodeURIComponent(type)}`,
        );
    }
};
