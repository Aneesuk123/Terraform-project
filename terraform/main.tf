
# ------------------------------
# Resource Group
# ------------------------------
resource "azurerm_resource_group" "rg" {
  name     = "terraform-rg1"
  location = "eastus"
}

# ------------------------------
# App Service Plan
# ------------------------------
resource "azurerm_service_plan" "asp" {
  name                = "terraform-appservice-plan"
  resource_group_name = azurerm_resource_group.rg.name
  location            = "southindia"
  os_type             = "Linux"
  sku_name            = "B1"
  
}

# ------------------------------
# Azure Container Registry
# ------------------------------
resource "azurerm_container_registry" "acr" {
  name                = "terraformacr1"
  resource_group_name = azurerm_resource_group.rg.name
  location            = "southindia"
  sku                 = "Basic"
  admin_enabled       = false
}

# ------------------------------
# MySQL Flexible Server
# ------------------------------
resource "azurerm_mysql_flexible_server" "mysql" {
  name                = "terraform-mysql-server00"
  resource_group_name = azurerm_resource_group.rg.name
  location            = "southindia"
  administrator_login = "mysqladmin"
  administrator_password = var.mysql_password
  sku_name            = "B_Standard_B1ms"
  #size_mb          = 32768
  version             = "8.0.21"
  storage {
    size_gb = 21    # 32GB (instead of 32768 MB)
  }
}

# =============================
# Backend Web App - Azure
# =============================
resource "azurerm_linux_web_app" "backend" {
  name                = "terraform-backend-app"
  resource_group_name = azurerm_resource_group.rg.name
  location            = "southindia"
  service_plan_id     = azurerm_service_plan.asp.id
  https_only          = false

  identity {
    type = "SystemAssigned"
  }

  site_config {
    always_on       = false
    app_command_line = "/usr/sbin/apache2ctl -D FOREGROUND" # Only for php:apache
  }

  app_settings = {
    "DATABASE_HOST"     = azurerm_mysql_flexible_server.mysql.fqdn
    "DATABASE_USER"     = "mysqladmin@${azurerm_mysql_flexible_server.mysql.name}"
    "DATABASE_PASSWORD" = var.mysql_password
  }

  tags = {
    environment = "TerraformWebApp"
    project     = "RestaurantApp"
  }
}


# ------------------------------
# Frontend Web App (Docker)
# ------------------------------
resource "azurerm_linux_web_app" "frontend" {
  name                = "terraform-frontend-app"
  resource_group_name = azurerm_resource_group.rg.name
  location            = "southindia"
  service_plan_id     = azurerm_service_plan.asp.id
  https_only          = false

  identity {
    type = "SystemAssigned"
  }

  site_config {
    # Managed identity will pull the Docker image
    always_on = false
    app_command_line = "/bin/sh -c 'nginx -g \"daemon off;\"'"  # Only for nginx
  }

  app_settings = {
    "ENVIRONMENT" = "Production"
  }

  tags = {
    environment = "TerraformWebApp"
    project     = "RestaurantApp"
  }
}


# Give Backend Web App permission to pull images from ACR
resource "azurerm_role_assignment" "backend_acr_pull" {
  scope              = azurerm_container_registry.acr.id
  role_definition_name = "AcrPull"
  principal_id       = azurerm_linux_web_app.backend.identity[0].principal_id
}


# Give Frontend Web App permission to pull images from ACR
resource "azurerm_role_assignment" "frontend_acr_pull" {
  scope              = azurerm_container_registry.acr.id
  role_definition_name = "AcrPull"
  principal_id       = azurerm_linux_web_app.frontend.identity[0].principal_id
}


