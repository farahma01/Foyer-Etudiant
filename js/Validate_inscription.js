/**
 * validate-inscription.js
 * Validation JavaScript complète pour le formulaire d'inscription
 * Couvre : champs obligatoires, regex, longueur/plage, logique (mots de passe)
 */

(function () {
    'use strict';

    /* ── Utilitaires ── */
    const $ = id => document.getElementById(id);
    const show = (el, msg) => { el.textContent = msg; el.classList.add('show'); };
    const hide = el => { el.textContent = ''; el.classList.remove('show'); };
    const markError   = (input, msgEl, msg) => { input.classList.add('error'); input.classList.remove('valid'); show(msgEl, msg); return false; };
    const markValid   = (input, msgEl)       => { input.classList.remove('error'); input.classList.add('valid'); hide(msgEl); return true; };

    /* ── Expressions régulières ── */
    const REGEX = {
        email   : /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
        phone   : /^(\+216)?[2459]\d{7}$/,
        cin     : /^\d{8}$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/,
        name    : /^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$/,
    };

    /* ── Règles de validation par champ ── */
    function validateNom(val) {
        if (!val.trim()) return 'Le nom est obligatoire.';
        if (!REGEX.name.test(val)) return 'Nom invalide (lettres uniquement, 2-50 caractères).';
        return null;
    }
    function validatePrenom(val) {
        if (!val.trim()) return 'Le prénom est obligatoire.';
        if (!REGEX.name.test(val)) return 'Prénom invalide (lettres uniquement, 2-50 caractères).';
        return null;
    }
    function validateEmail(val) {
        if (!val.trim()) return "L'email est obligatoire.";
        if (!REGEX.email.test(val)) return 'Format email invalide (ex: user@mail.com).';
        return null;
    }
    function validateTel(val) {
        if (!val.trim()) return 'Le téléphone est obligatoire.';
        if (!REGEX.phone.test(val.replace(/\s/g,''))) return 'Format invalide (ex: 22 345 678 ou +216 22 345 678).';
        return null;
    }
    function validateCIN(val) {
        if (!val.trim()) return 'Le CIN est obligatoire.';
        if (!REGEX.cin.test(val)) return 'CIN invalide (exactement 8 chiffres).';
        return null;
    }
    function validateUniversite(val) {
        if (!val.trim()) return "L'université est obligatoire.";
        if (val.trim().length < 3) return 'Nom trop court (min 3 caractères).';
        return null;
    }
    function validateAnnee(val) {
        const n = parseInt(val);
        if (!val) return "L'année d'étude est obligatoire.";
        if (isNaN(n) || n < 1 || n > 5) return "L'année doit être entre 1 et 5.";
        return null;
    }
    function validatePassword(val) {
        if (!val) return 'Le mot de passe est obligatoire.';
        if (!REGEX.password.test(val)) return 'Min 8 caractères, 1 maj, 1 min, 1 chiffre, 1 caractère spécial.';
        return null;
    }
    function validateConfirm(val, pwd) {
        if (!val) return 'Veuillez confirmer le mot de passe.';
        if (val !== pwd) return 'Les mots de passe ne correspondent pas.';
        return null;
    } 
    function validateFoyer(val) {
        if (!val) return 'Veuillez sélectionner un foyer.';
        return null;
    }
    function validateTypeChambre(val) {
        if (!val) return 'Veuillez choisir un type de chambre.';
        return null;
    }
    function validateDateEntree(val) {
        if (!val) return "La date d'entrée est obligatoire.";
        const d = new Date(val);
        if (isNaN(d.getTime())) return 'Date invalide.';
        if (d < new Date()) return "La date d'entrée ne peut pas être dans le passé.";
        return null;
    }
    function validateCGU(checked) {
        if (!checked) return 'Vous devez accepter les conditions générales.';
        return null;
    }

    /* ── Validation d'un seul champ (appelée aussi en temps réel) ── */
    function validateField(fieldId) {
        const input  = $(fieldId);
        const msgEl  = $('err-' + fieldId);
        if (!input || !msgEl) return true;

        let err = null;
        switch (fieldId) {
            case 'nom'          : err = validateNom(input.value); break;
            case 'prenom'       : err = validatePrenom(input.value); break;
            case 'email'        : err = validateEmail(input.value); break;
            case 'tel'          : err = validateTel(input.value); break;
            case 'cin'          : err = validateCIN(input.value); break;
            case 'universite'   : err = validateUniversite(input.value); break;
            case 'annee_etude'  : err = validateAnnee(input.value); break;
            case 'password'     : err = validatePassword(input.value); break;
            case 'confirm_pass' :
                err = validateConfirm(input.value, $('password') ? $('password').value : '');
                break;
            case 'foyer_id'     : err = validateFoyer(input.value); break;
            case 'type_chambre' : err = validateTypeChambre(input.value); break;
            case 'date_entree'  : err = validateDateEntree(input.value); break;
            case 'cgu'          : err = validateCGU(input.checked); break;
        }
        return err ? markError(input, msgEl, err) : markValid(input, msgEl);
    }

    /* ── Force du mot de passe (jauge) ── */
    function updatePasswordStrength(val) {
        const bar   = $('strength-bar');
        const label = $('strength-label');
        if (!bar) return;
        let score = 0;
        if (val.length >=  8)        score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[a-z]/.test(val))        score++;
        if (/\d/.test(val))           score++;
        if (/[\W_]/.test(val))        score++;
        const levels = ['', 'Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];
        const colors = ['', '#ef476f',    '#ff8c42',  '#ffd166', '#06d6a0', '#0066cc'];
        bar.style.width  = (score * 20) + '%';
        bar.style.background = colors[score] || '#dee2e6';
        if (label) label.textContent = levels[score] || '';
    }

    /* ── Initialisation ── */
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-inscription');
        if (!form) return;

        /* Validation en temps réel sur blur */
        const fields = ['nom','prenom','email','tel','cin','universite',
                        'annee_etude','password','confirm_pass',
                        'foyer_id','type_chambre','date_entree'];
        fields.forEach(id => {
            const el = $(id);
            if (el) {
                el.addEventListener('blur',  () => validateField(id));
                el.addEventListener('input', () => { if (el.classList.contains('error')) validateField(id); });
            }
        });

        /* Jauge mot de passe */
        const pwdEl = $('password');
        if (pwdEl) pwdEl.addEventListener('input', () => updatePasswordStrength(pwdEl.value));

        /* Re-valider confirm quand le mot de passe change */
        if (pwdEl) pwdEl.addEventListener('input', () => {
            if ($('confirm_pass') && $('confirm_pass').classList.contains('error'))
                validateField('confirm_pass');
        });

        /* Soumission */
        form.addEventListener('submit', function (e) {
            const allFields = [...fields, 'cgu'];
            let valid = true;
            allFields.forEach(id => { if (!validateField(id)) valid = false; });

            if (!valid) {
                e.preventDefault();
                /* Scroller vers la première erreur */
                const firstErr = form.querySelector('.error');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
})();