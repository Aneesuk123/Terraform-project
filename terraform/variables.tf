variable "project_name" {
  default = "project2"
}

variable "frontend_image_name" {
  default = "frontend:v1"
}

variable "backend_image_name" {
  default = "backend:v1"
}

variable "mysql_password" {
  type      = string
  sensitive = true
}
