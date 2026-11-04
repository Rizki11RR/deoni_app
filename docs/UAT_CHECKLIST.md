# UAT checklist

Manual checks remain required because printer hardware and staff workflow cannot be verified by PHPUnit.

## Login and permissions

- [ ] Login each demo role.
- [ ] Confirm dashboard menu matches role.
- [ ] Confirm unauthorized URL returns 403 and shows error page.
- [ ] Logout, then login again after permission changes.

## Master data

- [ ] Create UoM category.
- [ ] Create base UoM.
- [ ] Create smaller and bigger UoM.
- [ ] Confirm cross-category UoM is rejected.
- [ ] Create product with stock, purchase, and sales UoM.
- [ ] Create product packaging with product-specific quantity.

## Sales and stock

- [ ] Create POS order.
- [ ] Create store order with customer.
- [ ] Create order using alternate UoM.
- [ ] Confirm displayed price remains product price.
- [ ] Confirm stock reservation uses base quantity.
- [ ] Confirm insufficient stock shows alert.
- [ ] Cancel order and confirm reservation release.

## Bon and payments

- [ ] Create bon without due date.
- [ ] Create bon with due date.
- [ ] Cashier approves bon.
- [ ] Confirm overdue reminder appears.
- [ ] Confirm overdue customer can create new order.
- [ ] Record partial and multi-method payment.
- [ ] Confirm reversal requires authorized role.

## Printing

- [ ] Open receipt on target printer.
- [ ] Confirm two-copy output.
- [ ] Confirm paper width, margins, line wrapping, and readable IDR values.

## Error and responsive states

- [ ] Submit empty/invalid forms and confirm visible red alert.
- [ ] Trigger duplicate code/category and confirm alert.
- [ ] Test empty list states.
- [ ] Test phone viewport without horizontal overflow.
- [ ] Check browser console during main workflows.
