/**
 * debounce Function
 * prevents the API from being hit for every single keystroke.
 */
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

async function fetchProducts(searchTerm, categoryId) {
    try {
        const params = new URLSearchParams();
        if (searchTerm) params.append("q", searchTerm);
        if (categoryId) params.append("category", categoryId);

        const baseUrl =
            typeof APP_BASE_URL !== "undefined"
                ? APP_BASE_URL.replace(/\/$/, "")
                : "";
        const url = `${baseUrl}/api/products/search?${params.toString()}`;

        const response = await fetch(url, {
            headers: { Accept: "application/json" },
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const data = await response.json();
        return data.products || [];
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

function renderProducts(products) {
    const resultsContainer = document.getElementById("product-list");

    resultsContainer.innerHTML = "";

    if (!products || products.length === 0) {
        resultsContainer.innerHTML =
            '<div class="col-12 text-center"><p>No products found.</p></div>';
        return;
    }

    products.forEach((product) => {
        resultsContainer.appendChild(createProductCard(product));
    });
}

function createProductCard(product) {
    const baseUrl =
        typeof APP_BASE_URL !== "undefined"
            ? APP_BASE_URL.replace(/\/$/, "")
            : "";

    //  for Image Path
    const imgSrc = product.image_path
        ? `${baseUrl}/${String(product.image_path).replace(/^\//, "")}`
        : null;

    const col = document.createElement("div");
    col.className = "col-md-4 mb-4 product-item";

    col.innerHTML = `
        <div class="card h-100">
            ${
                imgSrc
                    ? `<img src="${imgSrc}" class="card-img-top" alt="${escapeHtml(
                          product.name
                      )}" style="height:200px; object-fit:cover;">`
                    : `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                     <span class="text-muted">No Image</span>
                   </div>`
            }

            <div class="card-body position-relative">
                <form action="${baseUrl}/wishlist/toggle" method="POST" class="position-absolute top-0 end-0 p-2">
                    <input type="hidden" name="product_id" value="${
                        product.id
                    }">
                    <button type="submit" class="btn btn-link p-0 text-decoration-none" title="Wishlist">
                        <i class="bi ${
                            product.is_wishlisted
                                ? "bi-heart-fill text-danger"
                                : "bi-heart text-muted"
                        }" style="font-size: 1.5rem;"></i>
                    </button>
                </form>

                <h5 class="card-title">${escapeHtml(product.name)}</h5>
                <p class="card-text text-muted">${escapeHtml(
                    product.category_name || "Uncategorized"
                )}</p>
                <p class="card-text">${escapeHtml(
                    (product.description || "").substring(0, 100)
                )}...</p>
                <h6 class="card-subtitle mb-2 text-primary">$${parseFloat(
                    product.price
                ).toFixed(2)}</h6>
            </div>

            <form action="${baseUrl}/cart/add" method="POST" class="d-grid gap-2 px-3 pb-3">
                <input type="hidden" name="product_id" value="${product.id}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary">Add to Cart</button>
            </form>
        </div>
    `;

    return col;
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    const categoryFilter = document.getElementById("category-filter");

    async function performSearch() {
        const searchTerm = searchInput.value.trim();
        const categoryId = categoryFilter ? categoryFilter.value : "";

        const products = await fetchProducts(searchTerm, categoryId);

        renderProducts(products);
    }

    const debouncedSearch = debounce(performSearch, 300);

    if (searchInput) {
        searchInput.addEventListener("input", debouncedSearch);

        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                searchInput.value = "";
                if (categoryFilter) categoryFilter.value = "";
                performSearch();
            }
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener("change", performSearch);
    }
});
