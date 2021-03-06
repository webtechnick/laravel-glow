<?php

namespace WebTechNick\LaravelGlow\Libs;

use WebTechNick\LaravelGlow\Libs\LineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Cart
{
    /**
     * Collection of LineItem
     * @var [LineItem]
     */
    public $items;

    /**
     * Use the request object to talk to the session
     * @param Request $request [description]
     */
    public function __construct()
    {
        $this->items = collect($this->getFromSource());
    }

    /**
     * Add an item to the cart
     * @param Item $item [description]
     */
    public function add($item, $qty = 1)
    {
        $cart = new LineItem($item, $qty);
        $this->items[$item->id] = $cart;
        $item->incrimentCart();
        $this->save();
    }

    /**
     * Add an item to the cart
     * But we first want to look for the item, if it's already there, increase the qty.
     * @param Item    $item [description]
     * @param integer $qty  [description]
     */
    public function addToCart($item, $qty = 1)
    {
        if (isset($this->items[$item->id])) {
            return; // Do nothing.
        }
        return $this->add($item, $qty);
    }

    /**
     * Remove an item from the cart
     * @param  Item   $item [description]
     * @return [type]       [description]
     */
    public function remove($item)
    {
        $this->items = $this->items->reject(function($cart) use ($item) {
            return $cart->item->id == $item->id;
        });
        $this->save();
    }

    /**
     * Change the quantity in a cart item.
     * @param  Item   $item [description]
     * @param  [type] $qty  [description]
     * @return [type]       [description]
     */
    public function change($item, $qty)
    {
        $this->items[$item->id]->qty = $qty;
        $this->save();
    }

    /**
     * Clear the cart
     * @return [type] [description]
     */
    public function clear()
    {
        $this->items = collect([]);
        $this->save();
    }

    /**
     * Get the count of items in the cart
     * @return [type] [description]
     */
    public function itemCount()
    {
        return $this->items->count();
    }

    /**
     * Calculate the total qty in the cart
     * @return [type] [description]
     */
    public function totalQty()
    {
        return $this->items->sum(function($cart) {
            return $cart->qty;
        });
    }

    /**
     * Text for the UI element.
     * @return [type] [description]
     */
    public function text()
    {
        $count = $this->itemCount();
        if (!$count) {
            return 'Empty';
        }
        return $count . ' ' . str_plural('Item', $count);
    }

    public function getFromSource()
    {
        if (Auth::check()) {
            return $this->getCartFromDatabase();
        } else {
            return $this->getFromSession();
        }
    }

    /**
     * Retrieve the cart from the session
     * @return [type] [description]
     */
    public function getFromSession()
    {
        if (session()) {
            return session()->get('cart');
        }
        return [];
    }

    /**
     * Users can store their cart for extended periods of time.
     * Retrieve them.
     * @return [type] [description]
     */
    public function getCartFromDatabase()
    {
        // TODO
        return session()->get('cart') ?: [];
    }

    /**
     * Get the current cart
     * @return [type] [description]
     */
    public function get()
    {
        return $this->items->all();
    }

    /**
     * Is the current cart empty?
     * @return boolean [description]
     */
    public function isEmpty()
    {
        return $this->itemCount() == 0;
    }

    /**
     * Create the checkout script
     */
    public function checkout()
    {
        $key = config('services.stripe.key');
        $amount = $this->subTotal() * 100;
        $email = Auth::check() ? Auth::user()->email : '';

        return
            '<script
                src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                data-key="'. $key .'"
                data-amount="'. $amount .'"
                data-name="Mandi Makes Shop"
                data-billing-address="true"
                data-shipping-address="true"
                data-zip-code="true"
                data-email="'. $email .'"
                data-description="Purchase From Mandi Makes"
                data-image="https://mandimakes.shop/images/logo-transparent-sm.png"
                data-locale="auto">
            </script>';
    }

    /**
     * Calculate the subtotal of the cart
     * @return [type] [description]
     */
    public function subTotal()
    {
        return $this->items->sum(function($cart) {
            return $cart->item->price() * $cart->qty;
        });
    }

    /**
     * Save will store the cart in the session or the database.
     * @return [type] [description]
     */
    protected function save()
    {
        return session()->put('cart', $this->get());
    }
}