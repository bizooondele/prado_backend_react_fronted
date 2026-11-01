module.exports = {
    collectCoverage: true,
    collectCoverageFrom: ['src/**/*.{js,jsx}'],
    coverageDirectory: 'coverage',
    testEnvironment: 'jsdom',
    setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
    transform: {
        '^.+\\.[jt]sx?$': 'babel-jest',
    },
    moduleNameMapper: {
        '\\.(css)$': '<rootDir>/test/styleMock.cjs',
        '\\.(png|svg)$': '<rootDir>/test/fileMock.cjs',
    },
}