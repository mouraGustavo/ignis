// Script para preencher automaticamente o formulário de inscrição usando o json do 4devs.com.br com os dados de uma pessoa fictícia.
(() => {
     // Cole aqui o objeto (se vier como array, use pessoas[0])
    const pessoa = {
        "nome": "Matheus Antonio Corte Real",
        "idade": 18,
        "data_nasc": "13/06/2008",
        "email": "matheus-cortereal88@achievecidadenova.com.br",
        "celular": "(61) 98686-7989"
    };

    const setValue = (selector, value, eventType = 'input') => {
        const el = document.querySelector(selector);
        if (!el) return console.warn('Campo não encontrado:', selector);
        const proto = el.tagName === 'TEXTAREA' ? HTMLTextAreaElement.prototype
                    : el.tagName === 'SELECT' ? HTMLSelectElement.prototype
                    : HTMLInputElement.prototype;
        Object.getOwnPropertyDescriptor(proto, 'value').set.call(el, value);
        el.dispatchEvent(new Event(eventType, { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const setChecked = (wireModel) => {
        const el = document.querySelector(`input[type=checkbox][wire\\:model="${wireModel}"]`);
        if (!el) return console.warn('Checkbox não encontrado:', wireModel);
        el.checked = true;
        el.dispatchEvent(new Event('change', { bubbles: true }));
    };

    setValue('#full_name', pessoa.nome);
    setValue('#phone', pessoa.celular);
    setValue('#email', pessoa.email);

    const [dia, mes, ano] = pessoa.data_nasc.split('/');
    setValue('#birth_date', `${ano}-${mes}-${dia}`); // input type=date exige AAAA-MM-DD

    setValue('#shirt_size', 'M', 'change'); // 4devs não gera isso, escolha manual

    ['term_truth', 'term_participation', 'term_emergency', 'term_image', 'term_rules']
        .forEach(setChecked);

    if (pessoa.idade < 18) {
        console.warn(`ATENÇÃO: ${pessoa.nome} tem ${pessoa.idade} anos — é menor de idade. Preencha manualmente o nome do responsável e envie um arquivo qualquer como "documento do responsável" (não dá pra automatizar upload de arquivo via console).`);
    }

    console.log('Preenchido — confira os dados e clique em "Continuar para Pagamento" manualmente.');
})();
