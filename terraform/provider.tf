terraform {
  required_providers {
    azurerm = { source = "hashicorp/azurerm" }
  }
  required_version = ">=1.4.0"
}

provider "azurerm" {
  features {}

  # Specify your Azure subscription and tenant
  subscription_id = "e2acb89c-49b4-4c60-9a57-20af6e5ec3da"
  tenant_id       = "ab38cc85-7809-4284-abda-882b94d3c44e"
}
