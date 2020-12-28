const gulp = require('gulp');
const noop = require('gulp-noop');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const wait = require('gulp-wait'); // в Ubuntu не нужен
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel'); // Дополнительно установить @babel/core @babel/preset-env
const rollup = require('gulp-better-rollup'); // Дополнительно установить rollup@^1
const resolve = require('@rollup/plugin-node-resolve'); // Для подлкючегия библиотек из node.js
const commonjs = require('@rollup/plugin-commonjs'); // Для подключения библиотек из node.js (es5)
const inject = require('@rollup/plugin-inject');
const mergeStream = require('merge-stream');
const browserSync = require('browser-sync');
const del = require('del');
const { argv } = require('yargs');
// const log = require('fancy-log');

gulp.task('clean', () => del('dist/**/*'));

gulp.task('compile:sass', () => {
  return gulp.src('scss/main.scss')
    .pipe(wait(250)) // Позволяет избежать ошибок при компиляции
    .pipe(sass({ outputStyle: 'expanded' }))
    .pipe(gulp.dest('css'));
});

gulp.task('bundle:css', () => {
  return gulp.src([
    'css/main.css',
    'css/fullpage.min.css',
    'css/anim-fx.min.css',
  ]).pipe(maps('init'))
    .pipe(autoprefixer())
    .pipe(concat('bundle.css'))
    .pipe(maps('write', '.')) // Если не указывать путь для sourcemap тогда он будет добавлен в css-файл
    .pipe(gulp.dest('dist'));
});

gulp.task('bundle:js', () => {
  return gulp.src('js/main.js')
    .pipe(maps('init'))
    .pipe(rollup({
      plugins: [
        resolve(), // Чтобы подключать сторонние библиотеки из node.js через import
        commonjs(), // Чтобы подключать сторонние библиотеки из node.js через require
        inject({
          $: 'jquery',
          jQuery: 'jquery',
        }),
      ],
    }, {
      format: 'iife',
      globals: { jquery: '$' },
      interop: false, // Убирает лишнюю проверку
    }))
    .pipe(babelify())
    .pipe(rename('bundle.js'))
    .pipe(maps('write', '.')) // Если не указывать путь для sourcemap тогда он будет добавлен в js-файл
    .pipe(gulp.dest('dist'));
});

gulp.task('dev', gulp.series(
  'clean',
  'compile:sass',
  'bundle:css',
  'bundle:js'
));

gulp.task('build', gulp.series('dev', () => {
  const stream1 = gulp.src('dist/bundle.css')
    .pipe(maps('init'))
    .pipe(cleanCSS())
    .pipe(maps('write', '.'))
    .pipe(gulp.dest('dist'));

  const stream2 = gulp.src('dist/bundle.js')
    .pipe(maps('init'))
    .pipe(uglify())
    .pipe(maps('write', '.'))
    .pipe(gulp.dest('dist'));

  return mergeStream(stream1, stream2);
}));

gulp.task('watch', gulp.series('dev', () => {
  browserSync.init({
    open: false,
    notify: true,
    server: { baseDir: './' },
  });

  gulp.watch('**/*.html')
    .on('change', browserSync.reload);

  gulp.watch('css/**/*.css')
    .on('change', hmr);

  gulp.watch('scss/**/*.scss')
    .on('change', gulp.series('compile:sass', hmr));

  gulp.watch('js/**/*.js')
    .on('change', gulp.series('bundle:js'));
}));

// hot module replacement
function hmr() {
  return getTaskFn('bundle:css')
    .pipe(browserSync.reload({ stream: true }));
}

function maps(method, ...args) {
  return argv.maps ? sourcemaps[method](...args) : noop();
}

function babelify() {
  return argv.babel ? babel({ presets: ['@babel/preset-env'] }) : noop();
}

function getTaskFn(taskName) {
  return gulp.registry().get(taskName).unwrap()();
}

// const gulp = require('gulp');
// const noop = require('gulp-noop');
// const concat = require('gulp-concat');
// const rename = require('gulp-rename');
// const wait = require('gulp-wait'); // в Ubuntu не нужен
// const sass = require('gulp-sass');
// const sourcemaps = require('gulp-sourcemaps');
// const autoprefixer = require('gulp-autoprefixer');
// const cleanCSS = require('gulp-clean-css');
// const uglify = require('gulp-uglify');
// const babel = require('gulp-babel'); // Дополнительно установить @babel/core @babel/preset-env
// const rollup = require('gulp-better-rollup'); // Дополнительно установить rollup@^1
// const resolve = require('@rollup/plugin-node-resolve'); // Для подлкючегия библиотек из node.js
// const commonjs = require('@rollup/plugin-commonjs'); // Для подключения библиотек из node.js (es5)
// const inject = require('@rollup/plugin-inject');
// const mergeStream = require('merge-stream');
// const browserSync = require('browser-sync');
// const del = require('del');
// const { argv } = require('yargs');
// // const log = require('fancy-log');

// gulp.task('clean', () => del('dist/**/*'));

// gulp.task('compile:sass', () => {
//   return gulp.src('src/scss/main.scss')
//     .pipe(wait(250)) // Позволяет избежать ошибок при компиляции
//     .pipe(sass({ outputStyle: 'expanded' }))
//     .pipe(gulp.dest('src/css'));
// });

// gulp.task('bundle:css', () => {
//   return gulp.src([
//     'src/css/main.css',
//     'src/css/fullpage.min.css',
//     'src/css/anim-fx.min.css',
//   ]).pipe(maps('init'))
//     .pipe(autoprefixer())
//     .pipe(concat('bundle.css'))
//     .pipe(maps('write', '.')) // Если не указывать путь для sourcemap тогда он будет добавлен в css-файл
//     .pipe(gulp.dest('dist'));
// });

// gulp.task('bundle:js', () => {
//   return gulp.src('src/js/main.js')
//     .pipe(maps('init'))
//     .pipe(rollup({
//       plugins: [
//         resolve(), // Чтобы подключать сторонние библиотеки из node.js через import
//         commonjs(), // Чтобы подключать сторонние библиотеки из node.js через require
//         inject({
//           $: 'jquery',
//           jQuery: 'jquery',
//         }),
//       ],
//     }, {
//       format: 'iife',
//       globals: { jquery: '$' },
//       interop: false, // Убирает лишнюю проверку
//     }))
//     .pipe(babelify())
//     .pipe(rename('bundle.js'))
//     .pipe(maps('write', '.')) // Если не указывать путь для sourcemap тогда он будет добавлен в js-файл
//     .pipe(gulp.dest('dist'));
// });

// gulp.task('dev', gulp.series(
//   'clean',
//   'compile:sass',
//   'bundle:css',
//   'bundle:js'
// ));

// gulp.task('build', gulp.series('dev', () => {
//   const stream1 = gulp.src('dist/bundle.css')
//     .pipe(maps('init'))
//     .pipe(cleanCSS())
//     .pipe(maps('write', '.'))
//     .pipe(gulp.dest('dist'));

//   const stream2 = gulp.src('dist/bundle.js')
//     .pipe(maps('init'))
//     .pipe(uglify())
//     .pipe(maps('write', '.'))
//     .pipe(gulp.dest('dist'));

//   return mergeStream(stream1, stream2);
// }));

// gulp.task('watch', gulp.series('dev', () => {
//   browserSync.init({
//     open: false,
//     notify: true,
//     server: { baseDir: './' },
//   });

//   gulp.watch('**/*.html')
//     .on('change', browserSync.reload);

//   gulp.watch('src/css/**/*.css')
//     .on('change', hmr);

//   gulp.watch('src/scss/**/*.scss')
//     .on('change', gulp.series('compile:sass', hmr));

//   gulp.watch('src/js/**/*.js')
//     .on('change', gulp.series('bundle:js'));
// }));

// // hot module replacement
// function hmr() {
//   return getTaskFn('bundle:css')
//     .pipe(browserSync.reload({ stream: true }));
// }

// function maps(method, ...args) {
//   return argv.maps ? sourcemaps[method](...args) : noop();
// }

// function babelify() {
//   return argv.babel ? babel({ presets: ['@babel/preset-env'] }) : noop();
// }

// function getTaskFn(taskName) {
//   return gulp.registry().get(taskName).unwrap()();
// }