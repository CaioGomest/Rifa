// Theme Manager - Gerencia o tema escuro/claro
class ThemeManager {
    constructor() {
        this.themeKey = 'theme';
        this.init();
    }

    init() {
        console.log('ThemeManager: Inicializando...');
        // Sincroniza com o tema do banco de dados
        this.syncWithDatabase();
        
        // Aplica o tema atual
        this.applyTheme();
        
        // Previne FOUC (Flash of Unstyled Content)
        this.preventFOUC();
    }

    syncWithDatabase() {
        const themeFromBank = this.getThemeFromBank();
        const savedTheme = localStorage.getItem(this.themeKey);
        
        console.log('ThemeManager: Tema do banco:', themeFromBank);
        console.log('ThemeManager: Tema salvo:', savedTheme);
        
        // Se não há tema salvo ou se o tema do banco é diferente do salvo, usa o do banco
        if (!savedTheme || savedTheme !== themeFromBank) {
            console.log('ThemeManager: Sincronizando com banco de dados...');
            localStorage.setItem(this.themeKey, themeFromBank);
        }
    }

    getThemeFromBank() {
        // Obtém o tema do banco através de uma variável PHP
        // Esta função será sobrescrita pelo PHP
        const theme = window.themeFromBank || 'light';
        console.log('ThemeManager: Obtendo tema do banco:', theme);
        
        // Mapeia os valores do banco para o JavaScript
        // 'escuro' no banco = 'dark' no JavaScript
        // 'padrao' no banco = 'light' no JavaScript
        if (theme === 'escuro') {
            return 'dark';
        } else if (theme === 'padrao') {
            return 'light';
        }
        
        return theme;
    }

    applyTheme() {
        const currentTheme = localStorage.getItem(this.themeKey);
        console.log('ThemeManager: Aplicando tema:', currentTheme);
        document.documentElement.classList.toggle('dark', currentTheme === 'dark');
    }

    preventFOUC() {
        // Esconde a página até que o tema seja aplicado
        document.documentElement.style.visibility = 'hidden';
        
        // Aguarda o DOM estar pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.showPage();
            });
        } else {
            this.showPage();
        }
    }

    showPage() {
        // Adiciona classe para indicar que o tema foi carregado
        document.documentElement.classList.add('theme-loaded');
        document.documentElement.style.visibility = 'visible';
        console.log('ThemeManager: Página carregada com tema aplicado');
    }

    toggleTheme() {
        const isDarkMode = document.documentElement.classList.toggle('dark');
        localStorage.setItem(this.themeKey, isDarkMode ? 'dark' : 'light');
        console.log('ThemeManager: Tema alternado para:', isDarkMode ? 'dark' : 'light');
        return isDarkMode;
    }

    setTheme(theme) {
        if (theme === 'dark' || theme === 'light') {
            document.documentElement.classList.toggle('dark', theme === 'dark');
            localStorage.setItem(this.themeKey, theme);
            console.log('ThemeManager: Tema definido para:', theme);
        }
    }

    getCurrentTheme() {
        return localStorage.getItem(this.themeKey) || 'light';
    }
}

// Inicializa o gerenciador de tema
window.themeManager = new ThemeManager();

// Função global para alternar o tema (usada nos botões)
function mudarModo() {
    return window.themeManager.toggleTheme();
}

// Função global para alternar o tema (alias)
function toggleTheme() {
    return window.themeManager.toggleTheme();
} 