export class ValidacoesCRM {
    static responsaveisFinanceiros(entity: any) {
        if (!entity.responsaveisfinanceiros || entity.responsaveisfinanceiros.length <= 0) {
            return 'O negócio necessita de um responsável!';
        } else {
            let qndPrincipal = 0;
            for (let n of entity.responsaveisfinanceiros) {
                if (n.principal) qndPrincipal++;
            }
            if (qndPrincipal === 0) return 'O negócio deve ter um responsável financeiro como principal!';
            if (qndPrincipal > 1) return 'O negócio deve ter apenas um responsável financeiro principal!';
            return null;
        }
    }
}