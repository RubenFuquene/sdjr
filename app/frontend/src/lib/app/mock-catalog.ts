export type AppStoreCatalogItem = {
  id: number;
  name: string;
  category: string;
  address: string;
  rating: number;
  reviews: number;
  price: number;
  originalPrice: number;
  available: number;
  pickupTime: string;
  deliveryTime: string;
  deliveryCost: number;
  description: string;
};

export const APP_STORE_CATALOG: AppStoreCatalogItem[] = [
  {
    id: 1,
    name: "Panaderia El Trigal",
    category: "Panaderia",
    address: "Calle 72 #10-34, Chapinero, Bogota",
    rating: 4.8,
    reviews: 124,
    price: 8000,
    originalPrice: 18000,
    available: 3,
    pickupTime: "6:00 p.m. - 7:00 p.m.",
    deliveryTime: "45 - 60 min",
    deliveryCost: 3500,
    description:
      "Bolsa sorpresa con pan del dia y reposteria variada. El contenido puede cambiar segun disponibilidad.",
  },
  {
    id: 2,
    name: "Cafe Amor Perfecto",
    category: "Cafeteria",
    address: "Cra 7 #45-23, Chapinero, Bogota",
    rating: 4.9,
    reviews: 87,
    price: 6000,
    originalPrice: 15000,
    available: 2,
    pickupTime: "7:00 p.m. - 8:00 p.m.",
    deliveryTime: "30 - 45 min",
    deliveryCost: 3000,
    description:
      "Bolsa sorpresa con productos de cafeteria, snacks y reposteria. Ideal para merienda o cena ligera.",
  },
  {
    id: 3,
    name: "Restaurante Sabor Local",
    category: "Comida rapida",
    address: "Calle 85 #15-40, Usaquen, Bogota",
    rating: 4.7,
    reviews: 64,
    price: 12000,
    originalPrice: 26000,
    available: 5,
    pickupTime: "8:00 p.m. - 9:00 p.m.",
    deliveryTime: "35 - 50 min",
    deliveryCost: 4000,
    description:
      "Bolsa sorpresa con platos preparados del dia. Puede incluir proteina, acompañamiento y bebida.",
  },
];

export function getStoreById(storeId: number): AppStoreCatalogItem | null {
  return APP_STORE_CATALOG.find((item) => item.id === storeId) ?? null;
}
