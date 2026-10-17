import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Chart = Chart;

/**
 * Éditeur de lignes pour devis, factures et achats.
 * Utilisation : x-data="lineEditor({ items, products, discount, taxRate, priceField })"
 */
Alpine.data('lineEditor', (config = {}) => ({
    items: config.items?.length ? config.items : [],
    products: config.products || [],
    discount: Number(config.discount || 0),
    taxRate: Number(config.taxRate || 0),
    priceField: config.priceField || 'unit_price',
    productPrice: config.productPrice || 'sale_price',

    seq: 0,

    init() {
        this.items = this.items.map(it => ({ ...it, product_id: it.product_id ?? '', _k: ++this.seq }));
        if (!this.items.length) this.add();
    },
    add() {
        this.items.push({ _k: ++this.seq, product_id: '', description: '', quantity: 1, [this.priceField]: 0 });
    },
    remove(i) {
        this.items.splice(i, 1);
        if (!this.items.length) this.add();
    },
    pick(i) {
        const p = this.products.find(p => String(p.id) === String(this.items[i].product_id));
        if (p) {
            this.items[i].description = p.name;
            this.items[i][this.priceField] = Number(p[this.productPrice]);
        }
    },
    lineTotal(it) {
        return (Number(it.quantity) || 0) * (Number(it[this.priceField]) || 0);
    },
    get subtotal() {
        return this.items.reduce((s, it) => s + this.lineTotal(it), 0);
    },
    get taxAmount() {
        return Math.max(0, this.subtotal - this.discount) * this.taxRate / 100;
    },
    get total() {
        return Math.max(0, this.subtotal - this.discount) + this.taxAmount;
    },
    currency: document.querySelector('meta[name="currency"]')?.content || '',
    fmt(n) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n || 0).replace(/[\u202f\u00a0]/g, ' ');
    },
    /** Montant formaté suivi de la devise : 1 150 000 GNF */
    money(n) {
        return (this.fmt(n) + ' ' + this.currency).trim();
    },
}));

/** Copier dans le presse-papier avec retour visuel. */
Alpine.data('copyable', (text) => ({
    copied: false,
    copy() {
        navigator.clipboard?.writeText(text);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },
}));

/**
 * Mot de passe masqué : récupéré à la demande (jamais présent dans le HTML).
 */
Alpine.data('secret', (url, field = 'password') => ({
    value: null,
    shown: false,
    loading: false,
    copied: false,
    async load() {
        if (this.value !== null) return;
        this.loading = true;
        try {
            const { data } = await window.axios.post(url);
            this.value = data[field] ?? '';
        } finally {
            this.loading = false;
        }
    },
    async toggle() {
        await this.load();
        this.shown = !this.shown;
    },
    async copy() {
        await this.load();
        navigator.clipboard?.writeText(this.value);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },
}));

window.Alpine = Alpine;
Alpine.start();
