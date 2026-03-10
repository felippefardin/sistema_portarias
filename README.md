# Sistema de Portarias - Manual de Instalação Rápida

Este sistema foi desenvolvido em PHP com banco de dados SQLite3, o que permite que ele seja movido entre computadores de forma muito simples, sem a necessidade de configurar servidores de banco de dados complexos (como MySQL).

##  Pré-requisitos
Para rodar o sistema, o computador "servidor" precisa de um ambiente PHP com a extensão **SQLite3** ativa.

### Opção 1: Laragon (Recomendado - Mais simples)
1. Baixe o **Laragon Portable** ou **Full**.
2. Extraia/Instale o Laragon.
3. Copie a pasta deste projeto para dentro de `C:/laragon/www/`.
4. No painel do Laragon, clique em **"Start All"**.
5. Acesse no navegador: `http://localhost/sistema_portarias/`.

### Opção 2: XAMPP
1. Instale o **XAMPP**.
2. Copie a pasta deste projeto para `C:/xampp/htdocs/`.
3. No Painel de Controle do XAMPP, inicie o **Apache**.
4. Acesse no navegador: `http://localhost/sistema_portarias/`.

##  Acesso em Rede Local (Outros PCs)
Se você quiser que outros computadores da mesma rede acessem o sistema:
1. No PC onde o sistema está instalado, abra o **Prompt de Comando** e digite `ipconfig`.
2. Anote o endereço **IPv4** (Ex: `192.168.1.50`).
3. Nos outros computadores, digite no navegador: `http://192.168.1.50/sistema_portarias/`.

##  Estrutura de Pastas Importante
* **data/**: Contém o banco de dados `portarias.db`. Não apague esta pasta.
* **fpdf/**: Biblioteca necessária para gerar os PDFs das portarias.
* **img/**: Contém a logomarca da Serra usada nos documentos.
