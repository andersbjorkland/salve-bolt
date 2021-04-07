

module.exports = {
    plugins: {
        tailwindcss: {config: './tailwind.config.js'},
        autoprefixer: {}
    }
}

/*
let tailwindcss = require('tailwindcss');

module.exports = {
    plugins: [
        tailwindcss('./tailwind.config.js'), // your tailwind.js configuration file path
        require('autoprefixer'),
        require('postcss-import')
    ]
}

 */