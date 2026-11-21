# Değişiklikler

## 4.0.0

- iyzico PHP SDK bağımlılığı kaldırıldı; istekler doğrudan IYZWSv2 ile imzalanır. PHP 8.1+, `omnipay/common` ^3.3
- Ödeme Formu (Checkout Form): `purchase()` başlatır, `completePurchase()` sonucu jetonla sunucudan sorgular
- Sipariş no ve tutar denetimi, dolandırıcılık incelemesi (`isPendingReview()`)
- `void()` iptal, `refund()` kalem bazında iade, `fetchTransaction()` ödeme ayrıntısı
- Pazaryeri alt üye iş yeri (`subMerchantKey`)
- iyzico sandbox'ında uçtan uca doğrulandı (onaylı ödeme, kısmi iade, iptal)
