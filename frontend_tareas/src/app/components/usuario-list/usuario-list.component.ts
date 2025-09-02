import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { UsuarioService } from '../../services/usuario.service';
import { Usuario } from '../../models/usuario.model';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-user-list',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './usuario-list.html',
  styleUrls: ['./usuario-list.css']
})
export class UserListComponent implements OnInit {
  users: Usuario[] = [];
  loading = false;
  error = '';

  constructor(private userService: UsuarioService) {}

  ngOnInit() {
    this.loadUsers();
  }

  loadUsers() {
    this.loading = true;
    this.error = '';
    
    this.userService.getUsers().subscribe({
      next: (users) => {
        this.users = users;
        this.loading = false;
      },
      error: (error) => {
        this.error = 'Error al cargar los usuarios';
        this.loading = false;
        console.error('Error:', error);
      }
    });
  }

  deleteUser(user: Usuario) {
    Swal.fire({
          title: `¿Estás seguro de que deseas eliminar al usuario "${user.nombre}"?`,
          text: "No es posible deshacer esta acción.",
          icon: "warning",
          confirmButtonText: "Eliminar",
          confirmButtonColor: "#3085d6",
          showCancelButton: true,
          cancelButtonText: "Cancelar",
          cancelButtonColor: "#d33",
        }).then((result) => {
          if (result.isConfirmed) {
              this.userService.deleteUser(user.id!).subscribe({
                next: () => {
                  this.loadUsers(); // Recargar la lista
                },
                error: (error) => {
                  this.error = 'Error al eliminar el usuario';
                    Swal.fire({
                    title: "Error!",
                    text: "No se pudo eliminar el usuario.",
                    icon: "error"
                  });
                }
              })
          } else {
            Swal.fire({
                  title: "Atención",
                  text: "Acción cancelada por el usuario",
                  icon: "warning"
                });
          }
        });
  }
}