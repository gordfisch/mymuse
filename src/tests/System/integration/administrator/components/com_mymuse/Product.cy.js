describe('Test in backend that the product form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();

    //create one category
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_mymuse');
    cy.get('#jform_title').should('exist').type('Uncategorized');
    cy.clickToolbarButton('Save & Close');
    cy.get('#system-message-container').contains('Category saved.').should('exist');
    cy.contains('Uncategorized');

    // Product page. Clear the filter
    cy.visit('/administrator/index.php?option=com_mymuse&view=products&filter=');
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__mymuse_product WHERE title LIKE 'Test%'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE extension = 'com_mymuse'");
  });


  it('can create an product', () => {
    cy.visit('/administrator/index.php?option=com_mymuse&view=product&layout=edit');
    cy.get('#jform_title').clear().type('Test product');
    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Item saved.').should('exist');
    cy.contains('Test product');
  });

  it('can change special status level of a test product', () => {
    cy.db_createProduct({ title: 'Test product Bunny' }).then((product) => {
      cy.visit(`/administrator/index.php?option=com_mymuse&view=product&layout=edit&id=${product.id}`);
      cy.get('#jform_access').select('Special');
      cy.clickToolbarButton('Save & Close');

      cy.get('td').contains('Special').should('exist');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_mymuse&view=product&layout=edit');
    cy.intercept('index.php?option=com_mymuse&view=products').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
