import { digitsOnly } from './lib/money';

/**
 * State keranjang kasir. Semua kalkulasi harga ada di sini (client) hanya untuk tampilan;
 * server tetap menghitung ulang total saat checkout (sumber kebenaran tunggal).
 */
export function registerPos() {
    return {
        query: '',
        category: '',
        products: [],
        loading: true,
        error: null,
        cart: [],
        paymentMethod: 'cash',
        paidInput: '',
        processing: false,
        receipt: null,
        searchTimer: null,
        controller: null,

        init() {
            this.fetchProducts();
            // Tombol "/" memfokuskan pencarian; Esc mengosongkan.
            window.addEventListener('keydown', (e) => {
                if (e.key === '/' && !this.isTyping(e)) { e.preventDefault(); this.$refs.search?.focus(); }
                if (e.key === 'F9') { e.preventDefault(); this.checkout(); }
            });
        },

        isTyping(e) {
            return ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
        },

        /** Debounce pencarian 250 ms + pembatalan request lama (AbortController). */
        onSearch() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.fetchProducts(), 250);
        },

        async fetchProducts() {
            this.controller?.abort();
            this.controller = new AbortController();
            this.loading = true;
            this.error = null;

            try {
                const { data } = await window.axios.get('/pos/products', {
                    params: { q: this.query, category: this.category },
                    signal: this.controller.signal,
                });
                this.products = data.data;
            } catch (err) {
                if (err.name !== 'CanceledError') this.error = 'Gagal memuat produk. Periksa koneksi lalu coba lagi.';
            } finally {
                this.loading = false;
            }
        },

        /** Enter pada kolom pencarian: jika hasil tepat satu barcode/SKU, langsung masukkan ke keranjang (mode scanner). */
        scanEnter() {
            const exact = this.products.find((p) => p.barcode === this.query || p.sku === this.query);
            if (exact) {
                this.add(exact);
                this.query = '';
                this.fetchProducts();
            }
        },

        add(product) {
            const line = this.cart.find((i) => i.id === product.id);
            const nextQty = (line?.quantity ?? 0) + 1;

            if (nextQty > product.stock) {
                this.error = `Stok ${product.name} tersisa ${product.stock}.`;
                return;
            }
            this.error = null;

            if (line) {
                line.quantity = nextQty;
            } else {
                this.cart.push({ id: product.id, name: product.name, price: product.price, stock: product.stock, quantity: 1 });
            }
        },

        setQty(item, qty) {
            const next = Math.max(0, Math.min(Number(qty) || 0, item.stock));

            if (next === 0) {
                this.remove(item);
                return;
            }
            item.quantity = next;
        },

        remove(item) {
            this.cart = this.cart.filter((i) => i.id !== item.id);
        },

        clear() {
            this.cart = [];
            this.paidInput = '';
        },

        get itemCount() {
            return this.cart.reduce((sum, i) => sum + i.quantity, 0);
        },

        get total() {
            return this.cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
        },

        get paid() {
            return digitsOnly(this.paidInput);
        },

        get change() {
            return Math.max(0, this.paid - this.total);
        },

        get canCheckout() {
            return this.cart.length > 0 && this.paid >= this.total && !this.processing;
        },

        /** Tombol nominal cepat: uang pas dan pecahan umum. */
        quickAmounts() {
            const t = this.total;
            const steps = [t, 20000, 50000, 100000].filter((v, i, arr) => v >= t && arr.indexOf(v) === i);
            return steps.slice(0, 4);
        },

        async checkout() {
            if (!this.canCheckout) return;
            this.processing = true;
            this.error = null;

            try {
                const { data } = await window.axios.post('/pos/checkout', {
                    items: this.cart.map((i) => ({ product_id: i.id, quantity: i.quantity })),
                    payment_method: this.paymentMethod,
                    amount_paid: this.paid,
                });
                this.receipt = data.data;
                this.clear();
                this.fetchProducts(); // stok terbaru
            } catch (err) {
                const messages = err.response?.data?.errors;
                this.error = messages ? Object.values(messages).flat()[0] : 'Checkout gagal. Transaksi belum tersimpan, silakan coba lagi.';
            } finally {
                this.processing = false;
            }
        },

        closeReceipt() {
            this.receipt = null;
        },
    };
}
