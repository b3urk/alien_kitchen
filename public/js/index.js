const btn_ingredient = document.querySelector('#btn_ingredient')
btn_ingredient.addEventListener('click', () => {
    console.log('clicked')
    
    const field = document.createElement('div')
    const recipe_ingredients = document.querySelector('#recipe_ingredients')
    const fields_lenght = recipe_ingredients.children.length
    
    field.innerHTML = recipe_ingredients 
                        .dataset
                        .prototype
                        .replace(
                            /__name__/g,
                            fields_lenght
                        )

    recipe_ingredients.appendChild(field)
    
})
