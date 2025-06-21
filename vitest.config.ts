import { mergeConfig, defineConfig, configDefaults } from 'vitest/config';
import viteConfig from './vite.config';

export default mergeConfig(
    viteConfig,
    defineConfig({
        test: {
            globals: true,
            environment: 'jsdom',
            exclude: [...configDefaults.exclude, 'e2e/**'],
            coverage: {
                provider: 'v8',
                include: ['resources/js/**/*.ts'],
                exclude: ['node_modules', 'vendor', 'public', 'resources/js/types', 'resources/js/**/__tests__/**', 'resources/js/*.ts'],
            },
        },
    }),
);
