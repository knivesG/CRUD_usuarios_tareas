import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { TareaService } from '../../services/tarea.service';
import { UsuarioService } from '../../services/usuario.service';
import { Tarea } from '../../models/tarea.model';
import { Usuario } from '../../models/usuario.model';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-task-list',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './tareas-list.html',
  styleUrls: ['./tareas-list.css']
})
export class TaskListComponent implements OnInit {
  tasks: Tarea[] = [];
  filteredTasks: Tarea[] = [];
  users: Usuario[] = [];
  loading = false;
  error = '';
  
  statusFilter = '';
  userFilter = '';

  constructor(
    private taskService: TareaService,
    private userService: UsuarioService
  ) {}

  ngOnInit() {
    this.loadTasks();
    this.loadUsers();
  }

loadTasks() {
  this.loading = true;
  this.error = '';
  
  this.taskService.getTasks().subscribe({
    next: (response) => {
      this.tasks = response;
      this.filteredTasks = response;
      this.loading = false;
      this.error = ''; 
    },
    error: (error) => {
      
      this.loading = false;
      this.error = 'Error al cargar las tareas';
    },
    complete: () => {
      console.log('Observable completado');
    }
  });
}

  loadUsers() {
    this.userService.getUsers().subscribe({
      next: (users) => {
        this.users = users;
      },
      error: (error) => {
        console.error('Error al cargar usuarios:', error);
      }
    });
  }

  applyFilters() {
    this.filteredTasks = this.tasks.filter(task => {
      const statusMatch = !this.statusFilter || task.estado === this.statusFilter;
      
      let userMatch = true;
      if (this.userFilter === 'unassigned') {
        userMatch = !task.usuario_id;
      } else if (this.userFilter) {
        userMatch = task.usuario_id?.toString() === this.userFilter;
      }

      return statusMatch && userMatch;
    });
  }

  getStatusLabel(status: string): string {
    const labels: { [key: string]: string } = {
      'pendiente': 'Pendiente',
      'en_progreso': 'En Progreso',
      'completada': 'Completada'
    };
    return labels[status] || status;
  }

  assignTask(task: Tarea, event: Event) {
    const userId = (event.target as HTMLSelectElement).value;
    if (userId) {
      this.taskService.assignTaskToUser(task.id!, parseInt(userId)).subscribe({
        next: () => {
          this.loadTasks(); // Recargar para actualizar la vista
        },
        error: (error) => {
          this.error = 'Error al asignar la tarea';
          console.error('Error:', error);
        }
      });
    }
  }

  unassignTask(task: Tarea) {
    Swal.fire({
      title: `¿Deseas desasignar la tarea "${task.titulo}"?`,
      text: "No es posible deshacer esta acción.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Desasignar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        this.taskService.unassignTaskFromUser(task.id!).subscribe({
          next: () => {
            Swal.fire({
              title: "Eliminado!",
              text: "La tarea ha sido eliminada.",
              icon: "success"
            });
            this.loadTasks();
          },
          error: (error) => {
            this.error = 'Error al desasignar la tarea';
            Swal.fire({
              title: "Error!",
              text: "No se pudo eliminar la tarea.",
              icon: "error"
            });
          }
        });
      } else {
        Swal.fire({
          title: "Atención",
          text: "Acción cancelada por el usuario",
          icon: "warning"
        });
      }
    });
  }

  deleteTask(task: Tarea) {
    Swal.fire({
      title: `¿Estás seguro de que deseas eliminar la tarea "${task.titulo}"?`,
      text: "No es posible deshacer esta acción.",
      icon: "warning",
      confirmButtonText: "Eliminar",
      confirmButtonColor: "#3085d6",
      showCancelButton: true,
      cancelButtonText: "Cancelar",
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (result.isConfirmed) {
          this.taskService.deleteTask(task.id!).subscribe({
          next: (response) => {
            Swal.fire({
              title: "Eliminado!",
              text: "La tarea ha sido eliminada.",
              icon: "success"
            });
            this.loadTasks(); // Recargar lista
          },
          error: (error) => {
            Swal.fire({
              title: "Error!",
              text: "No se pudo eliminar la tarea.",
              icon: "error"
            });
          }
        });
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