#!/bin/bash
#
# Hidrossolo — Deploy por FTP para o servidor de producao
#
#   ./deploy-ftp.sh --check      Testa a conexao e lista a pasta remota
#   ./deploy-ftp.sh --dry-run    Mostra o que seria enviado (nao envia nada)
#   ./deploy-ftp.sh --go         Envia de verdade
#   ./deploy-ftp.sh --go --delete   Envia e remove do servidor o que nao existe local
#   ./deploy-ftp.sh --wipe       APAGA todo o conteudo da pasta remota (irreversivel)
#
# As credenciais ficam em .ftp-deploy.conf (fora do Git).
#
set -euo pipefail

PROJECT_DIR="/root/hidrossolo"
CONF_FILE="${PROJECT_DIR}/.ftp-deploy.conf"
STAGE_DIR="/tmp/hidrossolo-deploy"
LOG_DIR="${PROJECT_DIR}/logs"
LOG_FILE="${LOG_DIR}/deploy-ftp-$(date +%Y%m%d_%H%M%S).log"

MODE=""
DELETE_FLAG=""

# ---------------------------------------------------------------- argumentos
while [ $# -gt 0 ]; do
    case "$1" in
        --check)   MODE="check" ;;
        --dry-run) MODE="dry-run" ;;
        --go)      MODE="go" ;;
        --wipe)    MODE="wipe" ;;
        --delete)  DELETE_FLAG="--delete" ;;
        -h|--help) sed -n '2,12p' "$0"; exit 0 ;;
        *) echo "Opcao desconhecida: $1 (use --check, --dry-run, --go, --wipe ou --delete)" >&2; exit 1 ;;
    esac
    shift
done

if [ -z "$MODE" ]; then
    sed -n '2,12p' "$0"
    exit 1
fi

# ------------------------------------------------------------------ validacao
[ -f "$CONF_FILE" ] || { echo "ERRO: $CONF_FILE nao encontrado." >&2; exit 1; }

# shellcheck disable=SC1090
set -a; . "$CONF_FILE"; set +a

FALTANDO=0
for v in FTP_HOST FTP_USER FTP_PASS FTP_PATH; do
    if [ -z "${!v:-}" ]; then
        echo "  - $v esta vazio" >&2
        FALTANDO=1
    fi
done
FTP_PORT="${FTP_PORT:-21}"

if [ "$FALTANDO" -eq 1 ]; then
    echo "" >&2
    echo "Preencha esses campos em: $CONF_FILE" >&2
    exit 1
fi

# O 'open' do lftp usa  -u usuario,senha  -> virgula e espaco quebrariam o comando
case "$FTP_USER" in *,*|*" "*|"") echo "ERRO: FTP_USER invalido." >&2; exit 1 ;; esac
case "$FTP_HOST" in *" "*|"")       echo "ERRO: FTP_HOST invalido." >&2; exit 1 ;; esac

# Garantir barra no fim do caminho remoto
case "$FTP_PATH" in */) ;; *) FTP_PATH="${FTP_PATH}/" ;; esac

mkdir -p "$LOG_DIR"

LFTP_TMP="$(mktemp)"
chmod 600 "$LFTP_TMP"

# O lftp imprime a URL de conexao (ftp://usuario:SENHA@host) na saida.
# Este filtro troca a senha por asteriscos antes de exibir ou gravar em log.
# Usa um padrao de URL em vez do valor literal, para nao depender de escaping.
mascarar() {
    sed -e 's#\(ftp://[^:]*:\)[^@]*@#\1********@#g'
}

limpar() {
    rm -f "$LFTP_TMP"
    [ "$MODE" = "go" ] || rm -rf "$STAGE_DIR"
}
trap limpar EXIT

# ------------------------------------------- cabecalho comum do script lftp
# Escrito com printf/%s para que caracteres especiais da senha nao sejam
# interpretados pelo shell.
{
    printf 'set ftp:ssl-allow no\n'
    printf 'set ftp:passive-mode on\n'
    printf 'set net:max-retries 3\n'
    printf 'set net:timeout 25\n'
    printf 'set net:reconnect-interval-base 5\n'
    printf 'set mirror:set-permissions false\n'
    printf 'set xfer:clobber on\n'
    printf 'open -u %s,%s -p %s %s\n' "$FTP_USER" "$FTP_PASS" "$FTP_PORT" "$FTP_HOST"
    printf 'mkdir -p %s\n' "$FTP_PATH"
    printf 'cd %s\n' "$FTP_PATH"
} > "$LFTP_TMP"

echo "============================================================="
echo " Servidor : ${FTP_HOST}:${FTP_PORT} (FTP)"
echo " Usuario  : ${FTP_USER}"
echo " Destino  : ${FTP_PATH}"
echo " Modo     : ${MODE} ${DELETE_FLAG:+(${DELETE_FLAG})}"
echo "============================================================="

# ------------------------------------------------------------------- --check
if [ "$MODE" = "check" ]; then
    printf 'pwd\n'          >> "$LFTP_TMP"
    printf 'ls -l\n'        >> "$LFTP_TMP"
    printf 'bye\n'          >> "$LFTP_TMP"

    echo "Testando conexao..."
    lftp -f "$LFTP_TMP" 2>&1 | mascarar | tee "$LOG_FILE"
    chmod 600 "$LOG_FILE" 2>/dev/null || true
    echo ""
    echo "Log: $LOG_FILE"
    exit 0
fi

# ------------------------------------------------------------------- --wipe
if [ "$MODE" = "wipe" ]; then
    WIPE_EMPTY="/tmp/hidrossolo-wipe-empty"
    rm -rf "$WIPE_EMPTY"
    mkdir -p "$WIPE_EMPTY"

    echo ""
    echo "###  MODO DE EXCLUSAO  ###"
    echo "Todo o conteudo de ${FTP_PATH} sera REMOVIDO. Nao ha lixeira."
    echo ""

    # mirror a partir de uma pasta vazia + --delete = apaga tudo no destino.
    # O --delete do lftp so remove apos espelhar com sucesso, e nunca remove
    # o proprio diretorio de destino.
    printf 'mirror -R --delete --verbose=1 %s .\n' "$WIPE_EMPTY" >> "$LFTP_TMP"
    printf 'bye\n' >> "$LFTP_TMP"

    set +e
    lftp -f "$LFTP_TMP" 2>&1 | mascarar | tee "$LOG_FILE"
    chmod 600 "$LOG_FILE" 2>/dev/null || true
    set -e

    rm -rf "$WIPE_EMPTY"

    echo ""
    echo "Conferindo o resultado..."
    VERIFY="$(mktemp)"
    chmod 600 "$VERIFY"
    {
        printf 'set ftp:ssl-allow no\n'
        printf 'set ftp:passive-mode on\n'
        printf 'open -u %s,%s -p %s %s\n' "$FTP_USER" "$FTP_PASS" "$FTP_PORT" "$FTP_HOST"
        printf 'cd %s\n' "$FTP_PATH"
        printf 'ls -la\n'
        printf 'bye\n'
    } > "$VERIFY"
    lftp -f "$VERIFY" 2>&1 | mascarar
    rm -f "$VERIFY"
    exit 0
fi

# --------------------------------------------------- staging (copia limpa)
echo ""
echo "Preparando copia limpa do projeto..."

rm -rf "$STAGE_DIR"
mkdir -p "$STAGE_DIR"

rsync -a \
    --exclude='/.git/' \
    --exclude='/.ftp-deploy.conf' \
    --exclude='/.env' \
    --exclude='/.env.producao' \
    --exclude='/.idea/' \
    --exclude='/.well-known/' \
    --exclude='/storage/backups/' \
    --exclude='/storage/cache/' \
    --exclude='/cache/' \
    --exclude='/logs/' \
    --exclude='*.log' \
    --exclude='*.tar.gz' \
    "${PROJECT_DIR}/" "${STAGE_DIR}/"

# O .env de producao entra no lugar do .env de desenvolvimento
if [ -f "${PROJECT_DIR}/.env.producao" ]; then
    cp "${PROJECT_DIR}/.env.producao" "${STAGE_DIR}/.env"
    ENV_ORIGEM="${PROJECT_DIR}/.env.producao"
else
    ENV_ORIGEM="(nenhum - crie o .env manualmente no servidor)"
fi

TOTAL_ARQUIVOS="$(find "$STAGE_DIR" -type f | wc -l)"
TOTAL_TAMANHO="$(du -sh "$STAGE_DIR" | cut -f1)"

echo ""
echo "============================================================="
echo " A enviar : ${TOTAL_ARQUIVOS} arquivos (${TOTAL_TAMANHO})"
echo " .env     : ${ENV_ORIGEM}"
echo "============================================================="

# ------------------------------------------------------------- --dry-run
if [ "$MODE" = "dry-run" ]; then
    printf 'mirror -R -n --verbose=1 --no-perms %s .\n' "$STAGE_DIR" >> "$LFTP_TMP"
    printf 'bye\n' >> "$LFTP_TMP"

    echo ""
    lftp -f "$LFTP_TMP" 2>&1 | mascarar | tee "$LOG_FILE" | head -60
    chmod 600 "$LOG_FILE" 2>/dev/null || true
    echo ""
    echo "(simulacao - nada foi enviado)"
    echo "Log completo: $LOG_FILE"
    rm -rf "$STAGE_DIR"
    exit 0
fi

# ------------------------------------------------------------------ --go
echo ""
echo "Enviando... (isso pode demorar alguns minutos)"
echo ""

{
    printf 'mirror -R %s --verbose=1 --parallel=4 --no-perms %s .\n' \
        "$DELETE_FLAG" "$STAGE_DIR"
    printf 'bye\n'
} >> "$LFTP_TMP"

set +e
lftp -f "$LFTP_TMP" 2>&1 | mascarar > "$LOG_FILE"
STATUS=${PIPESTATUS[0]}
set -e
chmod 600 "$LOG_FILE" 2>/dev/null || true

echo "-------------------------------------------------------------"
echo " Arquivos enviados : $(grep -c 'Transferring file' "$LOG_FILE" 2>/dev/null || echo 0)"
echo " Erros             : $(grep -ci 'error\|failed\|nao foi possivel' "$LOG_FILE" 2>/dev/null || echo 0)"
echo " Log               : $LOG_FILE"
echo "-------------------------------------------------------------"

if [ "$STATUS" -ne 0 ]; then
    echo "❌ lftp terminou com codigo ${STATUS}. Ultimas linhas:"
    tail -30 "$LOG_FILE"
    exit "$STATUS"
fi

echo "✅ Deploy concluido."
echo ""
echo "Proximos passos no servidor:"
echo "  1. Aponte o document root do dominio para: ${FTP_PATH}public"
echo "  2. Edite o arquivo .env em ${FTP_PATH} com os dados do banco"
echo "  3. Importe o dump do banco pelo phpMyAdmin"
