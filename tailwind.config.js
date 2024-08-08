module.exports = {
    content: [
      './resources/**/*.blade.php',
      './resources/**/*.js',
      './resources/**/*.vue',
    ],
    theme: {
      extend: {
        gradientColorStops: theme => ({
          'purple': '#9333ea',
          'pink': '#ec4899',
        }),
      },
    },
    plugins: [],
  }
