/**
 * validate-contact.js
 * Validation JavaScript complète pour le formulaire de contact
 * Couvre : champs obligatoires, regex email/téléphone, longueur min/max, select
 */

(function () {
    'use strict';

    const $ = id => document.getElementById(id);
    const show = (el, msg) => { el.textContent = msg; el.classList.add('show'); };
    const hide = el => { el.textContent = ''; el.classList.remove('show'); };
    const markError = (input, msgEl, msg) => {
        input.classList.add('error'); input.classList.remove('valid');
        show(msgEl, msg); return false;
    };
    const markValid = (input, msgEl) => {
        input.classList.remove('error'); input.classList.add('valid');
        hide(msgEl); return true;
    };

    const REGEX = {
        email : /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
        phone : /^(\+216)?[2459]\d{7}$/,
        name  : /^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,60}$/,
    };

    /* ── Règles ── */
    function validateNomContact(val) {
        if (!val.trim()) return 'Le nom complet est obligatoire.';
        if (!REGEX.name.test(val)) return 'Nom invalide (lettres, 2-60 caractères).';
        return null;
    }
    function validateEmailContact(val) {
        if (!val.trim()) return "L'email est obligatoire.";
        if (!REGEX.email.test(val)) return 'Format email invalide (ex: user@mail.com).';
        return null;
    }
    function validateTelContact(val) {
        // Téléphone optionnel mais s'il est rempli, doit être valide
        if (!val.trim()) return null;
        if (!REGEX.phone.test(val.replace(/\s/g,'')))
            return 'Numéro tunisien invalide (ex: 22 345 678).';
        return null;
    }
    function validateSujet(val) {
        if (!val) return 'Veuillez choisir un sujet.';
        return null;
    }
    function validateFoyerContact(val) {
        // Optionnel, mais si fourni min 3 chars
        if (!val.trim()) return null;
        if (val.trim().length < 3) return 'Nom du foyer trop court (min 3 caractères).';
        return null;
    }
    function validateMessage(val) {
        if (!val.trim()) return 'Le message est obligatoire.';
        if (val.trim().length < 20) return 'Message trop court (min 20 caractères).';
        if (val.trim().length > 1000) return 'Message trop long (max 1000 caractères).';
        return null;
    } 

    /* ── Validation d'un champ ── */
    function validateField(fieldId) {
        const input = $(fieldId);
        const msgEl = $('err-' + fieldId);
        if (!input || !msgEl) return true;

        let err = null;
        switch (fieldId) {
            case 'nom_contact'   : err = validateNomContact(input.value);   break;
            case 'email_contact' : err = validateEmailContact(input.value); break;
            case 'tel_contact'   : err = validateTelContact(input.value);   break;
            case 'sujet'         : err = validateSujet(input.value);        break;
            case 'foyer_contact' : err = validateFoyerContact(input.value); break;
            case 'message'       : err = validateMessage(input.value);      break;
        }
        return err ? markError(input, msgEl, err) : markValid(input, msgEl);
    }

    /* ── Compteur de caractères pour le textarea ── */
    function updateCharCount(val) {
        const counter = $('msg-count');
        if (!counter) return;
        const len = val.trim().length;
        counter.textContent = len + '/1000 caractères';
        counter.style.color = len > 900 ? '#ef476f' : len > 700 ? '#ff8c42' : '#6c757d';
    }

    /* ── Init ── */
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-contact');
        if (!form) return;

        const fields = ['nom_contact','email_contact','tel_contact',
                        'sujet','foyer_contact','message'];

        fields.forEach(id => {
            const el = $(id);
            if (el) {
                el.addEventListener('blur',  () => validateField(id));
                el.addEventListener('input', () => {
                    if (el.classList.contains('error')) validateField(id);
                    if (id === 'message') updateCharCount(el.value);
                });
            }
        });

        form.addEventListener('submit', function (e) {
            /* On valide uniquement les champs obligatoires à la soumission */
            const required = ['nom_contact','email_contact','sujet','message'];
            const optional = ['tel_contact','foyer_contact'];
            let valid = true;
            [...required, ...optional].forEach(id => { if (!validateField(id)) valid = false; });

            /* Ré-ignorer les optionnels vides (pas d'erreur = true) */
            if (!valid) {
                e.preventDefault();
                const firstErr = form.querySelector('.error');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
})();